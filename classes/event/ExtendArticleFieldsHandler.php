<?php namespace Lovata\GoodNews\Classes\Event;

use Lovata\GoodNews\Controllers\Articles;
use Lovata\GoodNews\Models\Article;
use Lovata\Toolbox\Models\Settings;
use Lovata\Toolbox\Classes\Event\AbstractBackendFieldHandler;

/**
 * Class ExtendArticleFieldsHandler
 * @package Lovata\GoodNews\Classes\Event
 */
class ExtendArticleFieldsHandler extends AbstractBackendFieldHandler
{
    /**
     * @inheritDoc
     */
    protected function extendFields($obWidget)
    {
        $bIsHiddenAdditionalFieldsFileupload = Settings::getValue('is_hidden_additional_fields_fileupload');

        if ($bIsHiddenAdditionalFieldsFileupload) {
            $this->updateFileuploadFields($obWidget);
        }
    }

    /**
     * Update fileupload fields
     * @param \Backend\Widgets\Form $obWidget
     */
    protected function updateFileuploadFields($obWidget)
    {
        foreach ($obWidget->getFields() as $obField) {
            if ($obField->config['type'] === 'fileupload') {
                $obField->config['useCaption'] = false;
            }
        }
    }
    /**
     * @inheritDoc
     */
    protected function getModelClass(): string
    {
        return Article::class;
    }

    /**
     * @inheritDoc
     */
    protected function getControllerClass(): string
    {
        return Articles::class;
    }
}
