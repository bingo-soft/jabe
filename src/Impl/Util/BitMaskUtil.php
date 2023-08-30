<?php

namespace Jabe\Impl\Util;

use  Jabe\Impl\ProcessEngineLogger;

class BitMaskUtil
{
    //private static final EngineUtilLogger LOG = ProcessEngineLogger.UTIL_LOGGER;

    // First 8 masks as constant to prevent having to math.pow() every time a bit needs flippin'.
    private const FLAG_BIT_1 = 1;    // 000...00000001
    private const FLAG_BIT_2 = 2;    // 000...00000010
    private const FLAG_BIT_3 = 4;    // 000...00000100
    private const FLAG_BIT_4 = 8;    // 000...00001000
    private const FLAG_BIT_5 = 16;   // 000...00010000
    private const FLAG_BIT_6 = 32;   // 000...00100000
    private const FLAG_BIT_7 = 64;   // 000...01000000
    private const FLAG_BIT_8 = 128;  // 000...10000000

    private const MASKS = [self::FLAG_BIT_1, self::FLAG_BIT_2, self::FLAG_BIT_3, self::FLAG_BIT_4, self::FLAG_BIT_5, self::FLAG_BIT_6, self::FLAG_BIT_7, self::FLAG_BIT_8];

    /**
     * Set bit to '1' in the given int.
     * @param current integer value
     * @param bitNumber number of the bit to set to '1' (right first bit starting at 1).
     */
    public static function setBitOn(int $value, int $bitNumber): int
    {
        self::ensureBitRange($bitNumber);
        // To turn on, OR with the correct mask
        return $value | self::MASKS[$bitNumber - 1];
    }

    /**
     * Set bit to '0' in the given int.
     * @param current integer value
     * @param bitNumber number of the bit to set to '0' (right first bit starting at 1).
     */
    public static function setBitOff(int $value, int $bitNumber): int
    {
        self::ensureBitRange($bitNumber);
        // To turn on, OR with the correct mask
        return $value & ~self::MASKS[$bitNumber - 1];
    }

    /**
     * Check if the bit is set to '1'
     * @param value integer to check bit
     * @param number of bit to check (right first bit starting at 1)
     */
    public static function isBitOn(int $value, int $bitNumber): bool
    {
        self::ensureBitRange($bitNumber);
        return (($value & self::MASKS[$bitNumber - 1]) == self::MASKS[$bitNumber - 1]);
    }

    /**
     * Set bit to '0' or '1' in the given int.
     * @param current integer value
     * @param bitNumber number of the bit to set to '0' or '1' (right first bit starting at 1).
     * @param bitValue if true, bit set to '1'. If false, '0'.
     */
    public static function setBit(int $value, int $bitNumber, bool $bitValue): int
    {
        if ($bitValue) {
            return self::setBitOn($value, $bitNumber);
        } else {
            return self::setBitOff($value, $bitNumber);
        }
    }

    public static function getMaskForBit(int $bitNumber): int
    {
        return self::MASKS[$bitNumber - 1];
    }

    private static function ensureBitRange(int $bitNumber): void
    {
        if ($bitNumber <= 0 && $bitNumber > 8) {
            //throw LOG.invalidBitNumber(bitNumber);
            throw new \Exception("invalidBitNumber");
        }
    }
}
