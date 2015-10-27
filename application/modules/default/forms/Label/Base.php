<?php
class Default_Form_Label_Base extends Issues_FormAbstract
{
    public function init()
    {
        $element = new ZendX_JQuery_Form_Element_ColorPicker('color', array(
            'decorators'    => array(
                'UiWidgetElement'
            ),
            'validators' => array (
                array('Regex', false , array('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'))
            )
        ));
        $element->setValue('#000000');
        $this->addElement($element);

        $this->addElement('text', 'text', array(
            'filters'       => array('StringTrim', 'HtmlEntities'),
            'validators'    => array(
                array('StringLength', true, array(3, 50)),
                array('Db_NoRecordExists', true, array(
                    'adapter'   => Zend_Registry::get('Default_DiContainer')
                        ->getLabelMapper()
                        ->getReadAdapter(),
                    'table'     => Zend_Registry::get('Default_DiContainer')
                        ->getLabelMapper()
                        ->getTableName(),
                    'field'     => 'text'
                ))
            ),
            'decorators'    => array(
                'ViewHelper',
                new Zend_Form_Decorator_HtmlTag(array('tag' => 'label', 'id' => 'newLabel'))
            ),
            'required'      => true,
            'label'         => $this->translate('label_name')
        ));

        $this->addElement('button', 'submit', array(
            'required'   => false,
            'ignore'     => true,
            'decorators' => array('ViewHelper'),
            'type'       => 'submit',
            'escape'     => false,
        ));
        $this->setDecorators(array('FormElements','Form'));
    }
}
