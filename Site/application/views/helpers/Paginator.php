<?php

class Application_View_Helper_Paginator
{
    public static function getParameters($urlParameters, $additionalParameters)
    {
        if ($additionalParameters === null) {
            return $urlParameters;
        }
        return array_merge($urlParameters, $additionalParameters);
    }
    public static function getPageUrl($self, $page, $additionalParameters)
    {
        $urlParameters = self::getParameters(array('page' => $page), $additionalParameters);
        return $self->url($urlParameters);
    }
}
?>
