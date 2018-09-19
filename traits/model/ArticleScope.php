<?php namespace Lovata\GoodNews\Traits\Model;

use October\Rain\Argon\Argon;

/**
 * Trait ArticleScope
 * @package Lovata\GoodNews\Traits\Model
 * @author Sergey Zakharevich, s.zakharevich@lovata.com, LOVATA Group
 *
 * @method static $this getByStatus($sData)
 * @method static $this getByStatusIn($arData)
 * @method static $this getPublished()
 * @method static $this getByPublishedStart()
 * @method static $this getByPublishedStop()
 */

trait ArticleScope {
    /**
     * Get element by status_id value
     * @param \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder $obQuery
     * @param string $sData
     * @return \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder
     */
    public function scopeGetByStatus($obQuery, $sData)
    {
        if(!empty($sData)) {
            $obQuery->where('status_id', $sData);
        }

        return $obQuery;
    }

    /**
     * Get element by status_id value
     * @param \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder $obQuery
     * @param array $arData
     * @return \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder
     */
    public function scopeGetByStatusIn($obQuery, $arData)
    {
        if(!empty($arData)) {
            $obQuery->whereIn('status_id', $arData);
        }

        return $obQuery;
    }

    /**
     * Get published elements
     * @param \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder $obQuery
     * @return \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder
     */
    public function scopeGetPublished($obQuery)
    {
        $sDateNow = Argon::now()->format('Y-m-d H:i:s');

        return $obQuery->where('published_start', '<=', $sDateNow)
            ->where(function($obQuery) use ($sDateNow) {
                /** @var Article $obQuery */
                $obQuery->whereNull('published_stop')->orWhere('published_stop', '>', $sDateNow);
            });
    }

    /**
     * Get element by published_start value
     * @param \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder $obQuery
     * @return \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder
     */
    public function scopeGetByPublishedStart($obQuery)
    {
        $sData = Argon::now()->format('Y-m-d H:i:s');
        if(!empty($sData)) {
            $obQuery->where('published_stop', '>', $sData);
        }

        return $obQuery;
    }

    /**
     * Get element by published_stop value
     * @param \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder $obQuery
     * @return \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder
     */
    public function scopeGetByPublishedStop($obQuery)
    {
        $sData = Argon::now()->format('Y-m-d H:i:s');
        if(!empty($sData)) {
            $obQuery->where('published_stop', '>', $sData);
        }

        return $obQuery;
    }
}
