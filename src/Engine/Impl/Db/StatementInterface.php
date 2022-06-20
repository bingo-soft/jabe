<?php

namespace Jabe\Engine\Impl\Db;

interface StatementInterface
{
    /**
     * The constant indicating that the current <code>ResultSet</code> object
     * should be closed when calling <code>getMoreResults</code>.
     *
     * @since 1.4
     */
    public const CLOSE_CURRENT_RESULT = 1;

    /**
     * The constant indicating that the current <code>ResultSet</code> object
     * should not be closed when calling <code>getMoreResults</code>.
     *
     * @since 1.4
     */
    public const KEEP_CURRENT_RESULT = 2;

    /**
     * The constant indicating that all <code>ResultSet</code> objects that
     * have previously been kept open should be closed when calling
     * <code>getMoreResults</code>.
     *
     * @since 1.4
     */
    public const CLOSE_ALL_RESULTS = 3;

    /**
     * The constant indicating that a batch statement executed successfully
     * but that no count of the number of rows it affected is available.
     *
     * @since 1.4
     */
    public const SUCCESS_NO_INFO = -2;

    /**
     * The constant indicating that an error occurred while executing a
     * batch statement.
     *
     * @since 1.4
     */
    public const EXECUTE_FAILED = -3;

    /**
     * The constant indicating that generated keys should be made
     * available for retrieval.
     *
     * @since 1.4
     */
    public const RETURN_GENERATED_KEYS = 1;

    /**
     * The constant indicating that generated keys should not be made
     * available for retrieval.
     *
     * @since 1.4
     */
    public const NO_GENERATED_KEYS = 2;
}
