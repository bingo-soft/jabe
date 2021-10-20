<?php

namespace BpmPlatform\Engine\Impl\Db\EntityManager\Cache;

class DbEntityState
{
    /** A transient object does not exist in the database and has been created by the current session.
     * It will be INSERTed to the database and marked {@link #PERSISTENT} with the next flush.
     */
    public const TRANSIENT = 'transient';

    /** A persistent object has been loaded from the database by the current session.
     * At the next flush, the session will perform dirty checking and flush an update if the object's persistent state changed.
     * It will remain persistent after the next flush.
     */
    public const PERSISTENT = 'persistent';

    /** A persistent object which may exist in the database but which has not been loaded into the current session
     * form the database. A detached copy of the object has been modified offline and merged back into the session.
     * At the next flush an update with optimistic locking check will be performed and after that, the object will be marked
     * {@link #PERSISTENT}.
     */
    public const MERGED = 'merged';

    /** A transient object which does not exist in the database but has been created and deleted in the current session.
     * It will not be flushed to the database and will be removed from the cache at the next flush. */
    public const DELETED_TRANSIENT = 'deleted_transient';

    /** A persistent object which has been loaded into this session and will be deleted with the next flush.
     * After the flush it will be removed from the cache. */
    public const DELETED_PERSISTENT = 'deleted_persistent';

    /** A {@link #MERGED} object which may exists in the database and is set to be deleted by the current session.
     * It will be removed from the cache at the next flush. */
    public const DELETED_MERGED = 'deleted_merged';
}
