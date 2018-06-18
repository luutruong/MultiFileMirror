<?php
/**
 * @license
 * Copyright 2018 TruongLuu. All Rights Reserved.
 */
namespace Truonglv\MultiFileMirror\Option;

use XF\Option\AbstractOption;

class XFRMCategory extends AbstractOption
{
    public static function renderSelectMultiple(\XF\Entity\Option $option, array $htmlParams)
    {
        $choices = [];
        $controlOptions = self::getControlOptions($option, $htmlParams);
        $addOns = \XF::registry()->get('addOns');

        if (!empty($addOns['XFRM'])) {
            /** @var \XFRM\Repository\Category $categoryRepo */
            $categoryRepo = \XF::repository('XFRM:Category');

            $choices = $categoryRepo->getCategoryOptionsData(true);
            $choices = array_map(function ($v) {
                if (empty($v['label'])) {
                    $v['label'] = '(' . \XF::phrase('none') . ')';
                }

                $v['label'] = \XF::escapeString($v['label']);

                return $v;
            }, $choices);
        }

        $controlOptions['multiple'] = true;
        $controlOptions['size'] = 8;

        return self::getTemplater()->formSelectRow(
            $controlOptions,
            $choices,
            self::getRowOptions($option, $htmlParams)
        );
    }
}
