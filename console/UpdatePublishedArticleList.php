<?php namespace Lovata\GoodNews\Console;

use Illuminate\Console\Command;
use Lovata\GoodNews\Classes\Store\ArticleListStore;

/**
 * Class UpdatePublishedArticleList
 * @package Lovata\GoodNews\Console
 * @author Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class UpdatePublishedArticleList extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'UpdatePublishedArticleList';

    /**
     * @var string The console command description.
     */
    protected $description = 'UpdatePublishedArticleList';

    /**
     * Execute the console command.
     * @param ArticleListStore $obArticleListStore
     * @return void
     */
    public function fire(ArticleListStore $obArticleListStore)
    {
        $obArticleListStore->clearPublishedList();
        $obArticleListStore->getPublishedList();
    }
}