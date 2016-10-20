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

use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\HeadMeta as ZfHeadMeta;
use Zend\View\Exception;

/**
 * Facilitates insertion of OpenGraph meta tags.
 *
 * @see http://ogp.me
 *
 * Allows the following 'virtual' methods:
 * @method OpenGraph appendMusic($data)
 * @method OpenGraph prependMusic($data)
 * @method OpenGraph appendVideoData($data)
 * @method OpenGraph prependVideoData($data)
 * @method OpenGraph appendArticle($data)
 * @method OpenGraph prependArticle($data)
 * @method OpenGraph appendBook($data)
 * @method OpenGraph prependBook($data)
 * @method OpenGraph appendProfile($data)
 * @method OpenGraph prependProfile($data)
 *
 * @see http://ogp.me/#types
 *
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
class OpenGraph extends AbstractHelper
{
    const META_TYPE = 'property';
    const PROPERTY_SEPARATOR = ':';
    const GENERAL_NAMESPACE = 'og';

    /**
     * @var ZfHeadMeta
     */
    protected $headMeta;

    /**
     * @var bool
     */
    private $prependGeneralNamespace = true;

    /**
     * @return ZfHeadMeta
     */
    public function getHeadMeta()
    {
        if ($this->headMeta === null) {
            $this->setHeadMeta($this->getView()->plugin('headMeta'));
        }
        return $this->headMeta;
    }

    /**
     * @param \Zend\View\Helper\HeadMeta $headMeta
     * @return self
     */
    public function setHeadMeta(ZfHeadMeta $headMeta)
    {
        $this->headMeta = $headMeta;
        return $this;
    }

    protected function insertMeta($action, $property, $value)
    {
        $property = strtolower($property);

        if ($this->prependGeneralNamespace) {
            $property =sprintf("%s%s%s",self::GENERAL_NAMESPACE, self::PROPERTY_SEPARATOR , $property);
        }

        $headMeta = $this->getHeadMeta();

        $headMeta->$action($headMeta->createData(
            self::META_TYPE,
            $property,
            $value,
            array()
        ));
    }

    protected function insertStructure($action, $propertyType, $data, $level = 0)
    {
        if (is_array($data)) {
            if ($action == 'prepend' && $level == 0) {
                $data = $this->reverseData($data);
            }
            foreach ($data as $key => $value) {
                $property = $propertyType;
                if (is_string($key)) {
                    $property .= self::PROPERTY_SEPARATOR . $key;
                }
                $this->insertStructure($action, $property, $value, ++$level);
            }
        } else {
            $this->insertMeta($action, $propertyType, (string) $data);
        }
    }

    private function reverseData($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->reverseData($value);
            }
        }
        return array_reverse($data);
    }

    /**
     * Set 'type' property.
     *
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->insertMeta('set', 'type', $type);
        return $this;
    }

    /**
     * Set 'url' property.
     *
     * @param string $url
     * @return self
     */
    public function setUrl($url)
    {
        $this->insertMeta('set', 'url', $url);
        return $this;
    }

    /**
     * Set 'title' property.
     *
     * @param string $title
     * @return self
     */
    public function setTitle($title)
    {
        $this->insertMeta('set', 'title', $title);
        return $this;
    }

    /**
     * Set 'description' property.
     *
     * @param string $description
     * @return self
     */
    public function setDescription($description)
    {
        $this->insertMeta('set', 'description', $description);
        return $this;
    }

    /**
     * Set 'determiner' property.
     *
     * @param string $determiner
     * @return self
     */
    public function setDeterminer($determiner)
    {
        $this->insertMeta('set', 'determiner', $determiner);
        return $this;
    }

    /**
     * Set 'site_name' property.
     *
     * @param string $siteName
     * @return self
     */
    public function setSiteName($siteName)
    {
        $this->insertMeta('set', 'site_name', $siteName);
        return $this;
    }

    /**
     * Set 'locale' property.
     *
     * @param string $locale Default locale
     * @param array $alternates Alternate locales array
     * @return self
     */
    public function setLocale($locale, array $alternates = array())
    {
        $this->insertMeta('set', 'locale', $locale);

        if (!empty($alternates)) {
            foreach ($alternates as $alternate) {
                $this->insertStructure('append', 'locale', array(
                    'alternate' => $alternate
                ));
            }
        }

        return $this;
    }

    /**
     * Append 'image' property.
     *
     * @param string|array $data Image data (key => value array)
     * @return self
     */
    public function appendImage($data)
    {
        $this->insertStructure('append', 'image', $data);
        return $this;
    }

    /**
     * Prepend 'image' property.
     *
     * @param string|array $data Image data (key => value array)
     * @return self
     */
    public function prependImage($data)
    {
        $this->insertStructure('prepend', 'image', $data);
        return $this;
    }

    /**
     * Append 'video' property.
     *
     * @param string|array $data Video data (key => value array)
     * @return self
     */
    public function appendVideo($data)
    {
        $this->insertStructure('append', 'video', $data);
        return $this;
    }

    /**
     * Prepend 'video' property.
     *
     * @param string|array $data Video data (key => value array)
     * @return self
     */
    public function prependVideo($data)
    {
        $this->insertStructure('prepend', 'video', $data);
        return $this;
    }

    /**
     * Append 'audio' property.
     *
     * @param string|array $data Audio data (key => value array)
     * @return self
     */
    public function appendAudio($data)
    {
        $this->insertStructure('append', 'audio', $data);
        return $this;
    }

    /**
     * Prepend 'audio' property.
     *
     * @param string|array $data Audio data (key => value array)
     * @return self
     */
    public function prependAudio($data)
    {
        $this->insertStructure('prepend', 'audio', $data);
        return $this;
    }

    /**
     * Overload method access for custom OG property types.
     *
     * @param  string $method
     * @param  array  $args
     * @throws Exception\BadMethodCallException
     * @return self
     */
    public function __call($method, $args)
    {
        $matches = array();
        if (preg_match(
            '/^(?P<action>set|(pre|ap)pend)(?P<propType>[a-zA-Z0-9_]+)$/',
            $method,
            $matches)
        ) {
            $action = $matches['action'];
            $propertyType = $matches['propType'];

            if (empty($args)) {
                throw new Exception\BadMethodCallException("OpenGraph data is missing for the '$propertyType' property");
            }

            $data = $args[0];

            $this->prependGeneralNamespace = false;

            $this->insertStructure($action, $propertyType, $data);

            $this->prependGeneralNamespace = true;

            return $this;
        }

        throw new Exception\BadMethodCallException('Method "' . $method . '" does not exist');
    }
}
