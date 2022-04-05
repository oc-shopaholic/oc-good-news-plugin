<?php namespace Lovata\GoodNews\Classes\Store\Article;

use October\Rain\Argon\Argon;

use Kharanenka\Helper\CCache;

use Lovata\Toolbox\Classes\Store\AbstractStoreWithoutParam;

use Lovata\GoodNews\Models\Article;

/**
 * Class PublishedListStore
 * @package Lovata\GoodNews\Classes\Store\Article
 * @author  Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class PublishedListStore extends AbstractStoreWithoutParam
{
    const CACHE_TAG_DATA_PUBLISHED_START = 'lovata.goodnews.data_published_start';
    const CACHE_TAG_DATA_PUBLISHED_STOP = 'lovata.goodnews.data_published_stop';

    protected static $instance;

    /**
     * Get ID list from database
     * @return array
     */
    protected function getIDListFromDB() : array
    {
        $arElementIDList = (array) Article::getPublished()
            ->getByStatus(Article::STATUS_PUBLISHED)
            ->pluck('id')->all();

        return $arElementIDList;
    }

    /**
     * Get element ID list from array
     * @return array|null
     */
    protected function getIDListFromCache() : array
    {
        if ($this->isNeedUpdateCCacheDate()) {
            return [];
        }

        $arCacheTags = $this->getCacheTagList();
        $sCacheKey = $this->getCacheKey();

        $arElementIDList  = (array) CCache::get($arCacheTags, $sCacheKey);

        return $arElementIDList;
    }

    /**
     * Clear element ID list
     */
    public function clear()
    {
        parent::clear();

        CCache::clear([static::class], self::CACHE_TAG_DATA_PUBLISHED_START);
        CCache::clear([static::class], self::CACHE_TAG_DATA_PUBLISHED_STOP);
    }

    /**
     * Get first published start date
     * @return Argon
     */
    protected function getFirstPublishedStartDate()
    {
        $obResult = CCache::get([static::class], self::CACHE_TAG_DATA_PUBLISHED_START);
        if ($obResult !== null) {
            return $obResult;
        }

        $obDateNow = Argon::now();

        $obArticle = Article::whereNotNull('published_start')
            ->where('published_start', '>', $obDateNow->toDateTimeString())
            ->getByStatus(Article::STATUS_PUBLISHED)
            ->orderBy('published_start', 'asc')
            ->first();

        $obResult = !empty($obArticle) ? $obArticle->published_start : false;

        CCache::forever([static::class], self::CACHE_TAG_DATA_PUBLISHED_START, $obResult);

        return $obResult;
    }

    /**
     * Get first published stop date
     * @return Argon
     */
    protected function getFirstPublishedStopDate()
    {
        $obResult = CCache::get([static::class], self::CACHE_TAG_DATA_PUBLISHED_STOP);
        if ($obResult !== null) {
            return $obResult;
        }

        $obDateNow = Argon::now();

        $obArticle = Article::whereNotNull('published_stop')
            ->where('published_stop', '>', $obDateNow->toDateTimeString())
            ->getByStatus(Article::STATUS_PUBLISHED)
            ->orderBy('published_stop', 'asc')
            ->first();

        $obResult = !empty($obArticle) ? $obArticle->published_stop : false;

        CCache::forever([static::class], self::CACHE_TAG_DATA_PUBLISHED_STOP, $obResult);

        return $obResult;
    }

    /**
     * Check update cache date
     * Return true, if need update cache data
     * @return bool
     */
    protected function isNeedUpdateCCacheDate()
    {
        $obStartDate = $this->getFirstPublishedStartDate();
        $obStopDate = $this->getFirstPublishedStopDate();

        $obDateNow = Argon::now();
        $obDate = !empty($obStartDate) ? $obStartDate : $obStopDate;

        $bResult = (empty($obDate) && $obDate !== false) || (!empty($obDate) && $obDate < $obDateNow);

        return $bResult;
    }
}
