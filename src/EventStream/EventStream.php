<?php

namespace mad654\eventstore\EventStream;


use mad654\eventstore\Event;

/**
 * Interface EventStream
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
interface EventStream extends EventTraversable
{
    /**
     *
     * append single event to the end of this stream
     *
     * @param Event $event
     * @return EventStream
     */
    public function append(Event $event): EventStream;

    /**
     *
     * append all unknown events to the end of this stream
     *
     * @param EventStream $other
     */
    public function appendUnknown(EventStream $other): void;
}