<?php

namespace ride\web\template\twig;

use \Twig_Extension;
use \Twig_SimpleFilter;
use \Twig_SimpleFunction;

class TwigExtension extends Twig_Extension {

    /**
     * Gets the name of the extension
     * @return string Extension name
     */
    public function getName() {
        return 'web';
    }

    /**
     * Gets a list of functions to add to the existing list.
     * @return array An array of functions
     */
    public function getFunctions() {
        return array(
            new Twig_SimpleFunction('decorate', array($this, 'functionDecorate'), array('needs_context' => true)),
            new Twig_SimpleFunction('image', array($this, 'functionImage'), array('needs_context' => true)),
            new Twig_SimpleFunction('translate', array($this, 'functionTranslate'), array('needs_context' => true)),
            new Twig_SimpleFunction('url', array($this, 'functionUrl'), array('needs_context' => true)),
        );
    }

    /**
     * Gets a list of filters to add to the existing list.
     * @return array An array of filters
     */
    public function getFilters() {
        return array(
            new Twig_SimpleFilter('type', 'gettype'),
        );
    }

    /**
     * Retrieves the URL for a route
     * @param array $context
     * @param string $id
     * @param array $parameters
     * @return string URL of the id
     */
    public function functionDecorate($context, $value, $decorator) {
        if (!$decorator instanceof Decorator) {
            if (!$decorator || !is_string($decorator)) {
                throw new Exception('Could not decorate value: invalid decorator provided');
            }

            if (!isset($context['app']['system'])) {
                throw new Exception('Could not decorate value: system is not available in the app variable');
            }

            $decorator = $context['app']['system']->getDependencyInjector()->get('ride\\library\\decorator\\Decorator', $decorator);
        }

        return $decorator->decorate($value);
    }

    /**
     * Provides an url for the provided image
     * @param array $context
     * @param string $src Src of the image
     * @param string $default Default image for when no src is provided
     * @param array $parameters
     * @return string Url for the image
     * @todo extend to handle absolute URLs for thumbnailing
     */
    public function functionImage($context, $src, $default = null, array $parameters = null) {
        if (empty($src) && empty($default)) {
            throw new Exception('No src parameter provided for the image');
        }

        if (!$src) {
            $src = $default;
        }

        $thumbnailer = null;
        $width = 0;
        $height = 0;

        if (isset($parameters['thumbnail'])) {
            if (!isset($parameters['width'])) {
                $width = 0;
            } elseif (empty($parameters['width'])) {
                throw new Exception('Invalid width parameter provided for the thumbnailer');
            } else {
                $width = $parameters['width'];
            }

            if (!isset($parameters['height'])) {
                $height = 0;
            } elseif (empty($parameters['height'])) {
                throw new Exception('Invalid height parameter provided for the thumbnailer sqljfqmlskdjf');
            } else {
                $height = $parameters['height'];
            }

            $thumbnailer = $parameters['thumbnail'];
        }

        if (strncmp($src, 'http://', 7) !== 0 && strncmp($src, 'https://', 8) !== 0 && strncmp($src, '://', 3) !== 0) {
            if (!isset($context['app']['system'])) {
                throw new Exception('Could not load image ' . $src . ': system is not available in the app variable');
            }

            $imageUrlGenerator = $context['app']['system']->getDependencyInjector()->get('ride\\library\\image\\ImageUrlGenerator');
            $src = $imageUrlGenerator->generateUrl((string) $src, $thumbnailer, $width, $height);
        }

        return $src;
    }

    /**
     * Translates the provided key
     * @param array $context
     * @param string $key
     * @param array $parameters
     * @return string Translated key
     */
    public function functionTranslate($context, $key, array $parameters = null, $locale = null) {
        if (!isset($context['app']['system'])) {
            throw new Exception('Could not translate ' . $key . ': system is not available in the app variable');
        }

        $i18n = $context['app']['system']->getDependencyInjector()->get('ride\\library\\i18n\\I18n');
        $translator = $i18n->getTranslator($locale);

        return $translator->translate($key, $parameters);
    }

    /**
     * Retrieves the URL for a route
     * @param array $context
     * @param string $id
     * @param array $parameters
     * @return string URL of the id
     */
    public function functionUrl($context, $id, array $parameters = null) {
        if (!isset($context['app']['system'])) {
            throw new Exception('Could not get the URL for ' . $id . ': system is not available in the app variable');
        }

        $router = $context['app']['system']->getDependencyInjector()->get('ride\\library\\router\\Router');

        return $router->getRouteContainer()->getUrl($context['app']['url']['script'], $id, $parameters);
    }

}