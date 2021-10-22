<?php

namespace BpmPlatform\Engine\History;

interface ReportResultInterface
{
    /**
     * <p>Returns a period which specifies a time span within a year.</p>
     *
     * <p>The returned period must be interpreted in conjunction
     * with the returned {@link PeriodUnit} of {@link #getPeriodUnit()}.</p>
     *
     * </p>For example:</p>
     * <ul>
     *   <li>{@link #getPeriodUnit()} returns {@link PeriodUnit#MONTH}
     *   <li>{@link #getPeriod()} returns <code>3</code>
     * </ul>
     *
     * <p>The returned period <code>3</code> must be interpreted as
     * the third <code>month</code> of the year (i.e. it represents
     * the month March).</p>
     *
     * <p>If the {@link #getPeriodUnit()} returns {@link PeriodUnit#QUARTER},
     * then the returned period <code>3</code> must be interpreted as the third
     * <code>quarter</code> of the year.</p>
     *
     * @return an integer representing span of time within a year
     */
    public function getPeriod(): int;

    /**
     * <p>Returns the unit of the period.</p>
     *
     * @return a {@link PeriodUnit}
     *
     * @see #getPeriod()
     */
    public function getPeriodUnit(): string;
}
