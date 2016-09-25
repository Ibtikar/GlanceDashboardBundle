<?php

namespace Ibtikar\GlanceDashboardBundle\Translation;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class FallbackTranslator extends Translator
{
    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        /**
         * Support translating array of keys, check if the key is array then call trans recursively and return it as concatinated string with commas
         * @author Maisara Khedr <maisara@ibtikar.net.sa>
         */
        if(is_array($id)) {
            $translatedArray = array();
            foreach ($id as $element) {
                $translatedArray[] = $this->trans($element,array(),$domain);
            }
            $delimiter = ", ";
            if(isset($parameters['wordByWord']) && $parameters['wordByWord']){
                $delimiter=(isset($parameters['delimiter'])?$parameters['delimiter']:" ");
                if(isset($parameters['flip']) && $parameters['flip']){
                    $translatedArray = array_reverse($translatedArray);
                }
            }

            return implode($delimiter, $translatedArray);
        }
        /** end of  translating array support */

        if (null === $locale) {
            $locale = $this->getLocale();
        }

        if (null === $domain) {
            $domain = 'messages';
        }

        if (!isset($this->catalogues[$locale])) {
            $this->loadCatalogue($locale);
        }

        // Change translation domain to 'messages' if a translation can't be found in the
        // current domain
        if ('messages' !== $domain && false === $this->catalogues[$locale]->has((string) $id, $domain)) {
            $domain = 'messages';
        }

        foreach ($parameters as $key => $param) {
                if(strpos($key, '%') >= 0){
                    $parameters[$key] = $this->trans($param,array(),$domain);
                }
        }


        return strtr($this->catalogues[$locale]->get((string) $id, $domain), $parameters);
    }
}