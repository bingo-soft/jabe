<?php

namespace Jabe\Impl\Interceptor;

use Doctrine\DBAL\Exception\{
    ConstraintViolationException,
    DeadlockException,
    ForeignKeyConstraintViolationException,
    ServerException
};
use Jabe\ProcessEngineException;
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Cmd\CommandLogger;
use Jabe\Impl\ErrorCode\{
    BuiltinExceptionCode,
    ExceptionCodeProvider
};
use Jabe\Impl\Util\ExceptionUtil;

class ExceptionCodeInterceptor extends CommandInterceptor
{
    //protected static final CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    public const MIN_CUSTOM_CODE = 20000;
    public const MAX_CUSTOM_CODE = 39999;

    protected $builtinExceptionCodeProvider;
    protected $customExceptionCodeProvider;

    public function __construct(
        ?ExceptionCodeProvider $builtinExceptionCodeProvider,
        ?ExceptionCodeProvider $customExceptionCodeProvider
    ) {
        $this->builtinExceptionCodeProvider = $builtinExceptionCodeProvider;
        $this->customExceptionCodeProvider = $customExceptionCodeProvider;
    }

    public function execute(CommandInterface $command)
    {
        try {
            return $this->next->execute($command);
        } catch (ProcessEngineException $pex) {
            $this->assignCodeToException($pex);
            throw $pex;
        }
    }

    /**
     * <p>Built-in code provider has precedence over custom code provider and initial code (assigned via delegation code).
     * Custom and initial code is tried to be reset in case it violates the reserved code range.
     *
     * <p>When {@code disableBuiltInExceptionCodeProvider} flag
     * in {@link ProcessEngineConfigurationImpl} is configured to {@code true},
     * custom provider can override reserved codes.
     */
    protected function provideCodeBySupplier(
        $builtinSupplier,
        $customSupplier,
        int $initialCode
    ): int {
        $assignedByDelegationCode = $initialCode != BuiltinExceptionCode::FALLBACK;
        $builtinProviderConfigured = $this->builtinExceptionCodeProvider !== null;

        if ($builtinProviderConfigured) {
            $providedCode = $builtinSupplier();
            if ($providedCode !== null) {
                if ($assignedByDelegationCode) {
                    //LOG.warnResetToBuiltinCode(providedCode, initialCode);
                }
                return $providedCode;
            }
        }

        $customProviderConfigured = $this->customExceptionCodeProvider !== null;
        if ($customProviderConfigured && !$assignedByDelegationCode) {
            $providedCode = $customSupplier();
            if ($providedCode !== null && $builtinProviderConfigured) {
                return $this->tryResetReservedCode($providedCode);
            } else {
                return $providedCode;
            }
        } elseif ($builtinProviderConfigured) {
            return $this->tryResetReservedCode($initialCode);
        }
        return null;
    }

    protected function provideCode(ProcessEngineException $pex, int $initialCode): ?int
    {
        $sqlException = ExceptionUtil::unwrapException($pex);
        $builtinSupplier = null;
        $customSupplier = null;
        $builtinExceptionCodeProvider = $this->builtinExceptionCodeProvider;
        $customExceptionCodeProvider = $this->customExceptionCodeProvider;
        if ($sqlException !== null) {
            $builtinSupplier = function () use ($builtinExceptionCodeProvider, $sqlException) {
                return $builtinExceptionCodeProvider->provideCode($sqlException);
            };
            $customSupplier = function () use ($customExceptionCodeProvider, $sqlException) {
                return $customExceptionCodeProvider->provideCode($sqlException);
            };
        } else {
            $builtinSupplier = function () use ($builtinExceptionCodeProvider, $pex) {
                return $builtinExceptionCodeProvider->provideCode($pex);
            };
            $customSupplier = function () use ($customExceptionCodeProvider, $pex) {
                return $customExceptionCodeProvider->provideCode($pex);
            };
        }
        return $this->provideCodeBySupplier($builtinSupplier, $customSupplier, $initialCode);
    }

    /**
     * Resets codes to the {@link BuiltinExceptionCode#FALLBACK}
     * in case they are < {@link #MIN_CUSTOM_CODE} or > {@link #MAX_CUSTOM_CODE}.
     * No log is written when code is {@link BuiltinExceptionCode#FALLBACK}.
     */
    protected function tryResetReservedCode(?int $code): ?int
    {
        if ($this->codeReserved($code)) {
            //LOG.warnReservedErrorCode(code);
            return BuiltinExceptionCode::FALLBACK;
        } else {
            return $code;
        }
    }

    protected function codeReserved(?int $code): bool
    {
        return $code !== null && $code !== BuiltinExceptionCode::FALLBACK &&
          ($code < self::MIN_CUSTOM_CODE || $code > self::MAX_CUSTOM_CODE);
    }

    protected function assignCodeToException(ProcessEngineException $pex): void
    {
        $initialCode = $pex->getCode();
        $providedCode = $this->provideCode($pex, $initialCode);

        if ($providedCode !== null) {
            $pex->setCode($providedCode);
        }
    }
}
