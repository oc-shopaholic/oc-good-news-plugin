<?php namespace Lovata\GoodNews\Classes\Event;

use Lovata\GoodNews\Models\Article;
use Lovata\GoodNews\Models\Property;
use Lovata\GoodNews\Controllers\Articles;

/**
 * Class ExtendFieldHandler
 * @package Lovata\GoodNews\Classes\Event
 * @author  Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class ExtendFieldHandler
{
    /**
     * Add listeners
     * @param \Illuminate\Events\Dispatcher $obEvent
     */
    public function subscribe($obEvent)
    {
        $obEvent->listen('backend.form.extendFields', function ($obWidget) {
            $this->extendArticleFields($obWidget);
        });
    }

    /**
     * Extend fields for Article model
     * @param \Backend\Widgets\Form $obWidget
     */
    public function extendArticleFields($obWidget)
    {
        if (!$obWidget->getController() instanceof Articles || $obWidget->isNested) {
            return;
        }

        // Only for the Product model
        if (!$obWidget->model instanceof Article || $obWidget->context != 'update') {
            return;
        }

        $obPropertyList = Property::active()->orderBy('sort_order', 'asc')->get();
        if ($obPropertyList->isEmpty()) {
            return;
        }

        //Get widget data for properties
        $arAdditionPropertyData = [];
        /** @var Property $obProperty */
        foreach ($obPropertyList as $obProperty) {
            $arPropertyData = $obProperty->getWidgetData();
            if (!empty($arPropertyData)) {
                $arAdditionPropertyData[Property::NAME.'['.$obProperty->code.']'] = $arPropertyData;
            }
        }

        // Add fields
        if (!empty($arAdditionPropertyData)) {
            $obWidget->addTabFields($arAdditionPropertyData);
        }
    }
}
