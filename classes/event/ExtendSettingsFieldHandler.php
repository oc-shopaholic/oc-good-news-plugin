<?php namespace Lovata\GoodNews\Classes\Event;

use Lovata\Toolbox\Classes\Event\AbstractBackendFieldHandler;
use Lovata\Toolbox\Models\Settings;

class ExtendSettingsFieldHandler extends AbstractBackendFieldHandler
{
    /**
     * @inheritDoc
     */
    protected function extendFields($obWidget)
    {
        $arAdditionFieldList = [
            'is_hidden_additional_fields_fileupload' => [
                'tab'   => 'lovata.goodnews::lang.tab.settings',
                'label' => 'lovata.goodnews::lang.field.is_hidden_additional_fields_fileupload',
                'span'  => 'left',
                'type'  => 'checkbox',
            ],
        ];

        $obWidget->addTabFields($arAdditionFieldList);
    }

    /**
     * @inheritDoc
     */
    protected function getModelClass(): string
    {
        return Settings::class;
    }

    /**
     * @inheritDoc
     */
    protected function getControllerClass(): string
    {
        return \System\Controllers\Settings::class;
    }
}