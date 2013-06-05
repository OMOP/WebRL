<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    15 Dec 2010

    Decorator for MultiCheckbox element

    (c)2009-2010 Foundation for the National Institutes of Health (FNIH)

    Licensed under the Apache License, Version 2.0 (the "License"); you may not
    use this file except in compliance with the License. You may obtain a copy
    of the License at http://omop.fnih.org/publiclicense.

    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
    WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. Any
    redistributions of this work or any derivative work or modification based on
    this work should be accompanied by the following source attribution: "This
    work is based on work by the Observational Medical Outcomes Partnership
    (OMOP) and used under license from the FNIH at
    http://omop.fnih.org/publiclicense.

    Any scientific publication that is based on this work should include a
    reference to http://omop.fnih.org.

================================================================================*/
class Application_Form_Decorator_MultiCheckbox extends Zend_Form_Decorator_Abstract {

    protected $_format = "<div>\n<label for=\"%s\">%s</label>\n<input id=\"%s\" type=\"checkbox\" value=\"%s\" name=\"%s\" %s/></div>\n<div class=\"clear\"></div>\n";
    protected $_formatWithLabel = "<p><input id=\"%s\" type=\"checkbox\" value=\"%s\" name=\"%s\" %s/>%s</p>";

    public function render($content) {
        $element = $this->getElement();
        $options = $element->getMultiOptions();
        $id = $element->getId();
        $name = $element->getFullyQualifiedName();
        $value = $element->getValue();
        $allowed = $element->getAttrib('allowedEntries');
        $disabled = $element->getAttrib('disabled');
        $onlyChecked = $element->getAttrib('onlyChecked');
        $ifChecked = $element->getAttrib('checked');
        $markup = '';
        foreach($options as $key => $option) {
            if (is_array($value) && in_array($key, $value) || $ifChecked)
                $checked = 'checked="checked"';
            else
                $checked = '';
            if (is_array($allowed) && !in_array($key, $allowed) || $disabled)
                $checked .= 'disabled="disabled"';
            $option = preg_replace('/([^\s-]{7})([^\s-]{7})/', '$1&#8203;$2&#8203;', $option);
            if (! $onlyChecked || ($onlyChecked && is_array($value) && in_array($key, $value)))
                if ($element->getLabel() !== null ) {
                    $markup .= sprintf($this->_formatWithLabel, $id.'-'.$key, $key, $name, $checked, $option);
                } else {
                    $markup .= sprintf($this->_format, $id.'-'.$key, $option, $id.'-'.$key, $key, $name, $checked);
                }
        }
        $separator = $this->getSeparator();
        return $markup . $separator . $content;
    }

}