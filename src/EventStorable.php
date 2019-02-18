<?php

namespace mad654\eventstore;


/**
 * Interface EventStorable
 * @package mad654\eventstore
 *
 * Can store/load/traverse events
 * in the order they were attached originally
 *
 * It's import to know for a system in which order events
 * arrived. The timestamp of creation is "only" a part
 * of the domain knowledge and may be useful for merging
 * conflicting events or not.
 *
 * The order of events arrival at the store will give us
 * the reason, why this conflict occurred and make it repeatable.
 */
interface EventStorable extends EventTraversable
{
    public function attach(Event $event): EventStorable;

    public function importAll(EventStorable $other): void;
}