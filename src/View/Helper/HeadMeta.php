<?php
/**
 * This file is part of the ZfOpenGraph package.
 *
 * Copyright (c) Nikola Posa <posa.nikola@gmail.com>
 *
 * For full copyright and license information, please refer to the LICENSE file,
 * located at the package root folder.
 */

namespace Home\View\Helper;

use Zend\View\Helper\HeadMeta as DefaultHeadMeta;
use stdClass;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
class HeadMeta extends DefaultHeadMeta
{
    /**
     * Whether meta object should be validated based on currently set DOCTYPE.
     *
     * @var bool
     */
    protected $doctypeValidationEnabled = false;

    /**
     * @return bool
     */
    public function getDoctypeValidationEnabled()
    {
        return $this->doctypeValidationEnabled;
    }

    /**
     * @param bool $flag
     * @return HeadMeta
     */
    public function setDoctypeValidationEnabled($flag)
    {
        $this->doctypeValidationEnabled = (bool) $flag;
        return $this;
    }

    /**
     * Determine if item is valid
     *
     * @param  mixed $item
     * @return bool
     */
    protected function isValid($item)
    {
        if ((!$item instanceof stdClass)
            || !isset($item->type)
            || !isset($item->modifiers)
        ) {
            return false;
        }

        if ($this->doctypeValidationEnabled) {
            if (!isset($item->content)
                && (! $this->view->plugin('doctype')->isHtml5()
                || (! $this->view->plugin('doctype')->isHtml5() && $item->type !== 'charset'))
            ) {
                return false;
            }

            // <meta itemprop= ... /> is only supported with doctype html
            if (! $this->view->plugin('doctype')->isHtml5()
                && $item->type === 'itemprop'
            ) {
                return false;
            }

            // <meta property= ... /> is only supported with doctype RDFa
            if (!$this->view->plugin('doctype')->isRdfa()
                && $item->type === 'property'
            ) {
                return false;
            }
        }

        return true;
    }
}
