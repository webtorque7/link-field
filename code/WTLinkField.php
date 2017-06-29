<?php

class WTLinkField extends TextField
{

    private static $url_handlers = array(
        '$Action!/$ID' => '$Action'
    );

    private static $allowed_actions = array(
        'tree', 'InternalTree', 'FileTree'
    );

    protected $fieldField = null,
        $internalField = null,
        $externalField = null,
        $emailField = null,
        $fileField = null,
        $anchorField = null,
        $targetBlankField = null,
        $dataObjectField = null,
        $extraField = null;

    /**
     * @var FormField
     */
    protected $fieldType = null;

    /**
     * @var FormField
     */
    protected $fieldLink = null;


    /**
     * Used to cache data objects so we don't have to lookup more than once
     *
     * @var array
     */
    private static $linkable_data_objects = null;

    public function __construct($name, $title = null, $value = "")
    {
        // naming with underscores to prevent values from actually being saved somewhere
        $this->fieldType = new OptionsetField(
            "{$name}[Type]",
            _t('HtmlEditorField.LINKTO', 'Link to'),
            array(
                'Internal' => _t('HtmlEditorField.LINKINTERNAL', 'Page on the site'),
                'External' => _t('HtmlEditorField.LINKEXTERNAL', 'Another website'),
                'Email' => _t('HtmlEditorField.LINKEMAIL', 'Email address'),
                'File' => _t('HtmlEditorField.LINKFILE', 'Download a file'),
            ),
            'Internal'
        );


        $this->fieldLink = new CompositeField(
            $this->internalField = WTTreeDropdownField::create("{$name}[Internal]", _t('HtmlEditorField.Internal', 'Internal'), 'SiteTree', 'ID', 'Title', true),
            $this->externalField = TextField::create("{$name}[External]", _t('HtmlEditorField.URL', 'URL'), 'http://'),
            $this->emailField = EmailField::create("{$name}[Email]", _t('HtmlEditorField.EMAIL', 'Email address')),
            $this->fileField = WTTreeDropdownField::create("{$name}[File]", _t('HtmlEditorField.FILE', 'File'), 'File', 'ID', 'Title', true),
            $this->extraField = TextField::create("{$name}[Extra]", 'Extra(optional)')
                ->setDescription('Appended to url. Use to add sub-urls/actions or query string to the url, e.g. ?param=value'),
            $this->anchorField = TextField::create("{$name}[Anchor]", 'Anchor (optional)'),
            $this->targetBlankField = CheckboxField::create("{$name}[TargetBlank]", _t('HtmlEditorField.LINKOPENNEWWIN', 'Open link in a new window?'))
        );

        if ($linkableDataObjects = WTLink::get_data_object_types()) {

            if (!($objects = self::$linkable_data_objects)) {
                $objects = array();

                foreach ($linkableDataObjects as $className) {
                    $classObjects = array();

                    //set the format for DO value -> ClassName-ID
                    foreach (DataList::create($className) as $object) {
                        $classObjects[$className . '-' . $object->ID] = $object->Title;
                    }

                    if (!empty($classObjects)) {
                        $objects[singleton($className)->i18n_singular_name()] = $classObjects;
                    }
                }
            }

            if (count($objects)) {
                $this->fieldType->setSource(
                    array_merge(
                        $this->fieldType->getSource(),
                        array(
                            'DataObject' => _t('WTLinkField.LINKDATAOBJECT', 'Data Object'),
                        )
                    )
                );

                $this->fieldLink->insertBefore(
                    "{$name}[Extra]",
                    $this->dataObjectField = GroupedDropdownField::create(
                        "{$name}[DataObject]",
                        _t('WTLinkField.LINKDATAOBJECT', 'Link to a Data Object'),
                        $objects
                    )
                );
            }

            self::$linkable_data_objects = $objects;
        }

        $this->extraField->addExtraClass('no-hide');
        $this->anchorField->addExtraClass('no-hide');
        $this->targetBlankField->addExtraClass('no-hide');

        parent::__construct($name, $title, $value);
    }

    public function FileTree(SS_HTTPRequest $request)
    {
        return $this->fileField->tree($request);
    }

    public function InternalTree(SS_HTTPRequest $request)
    {
        return $this->internalField->tree($request);
    }

    /**
     * @return string
     */
    public function Field($properties = array())
    {
        Requirements::javascript(project() . '/javascript/WTLinkField.js');
        return "<div class=\"fieldgroup\">" .
            "<div class=\"fieldgroupField\">" . $this->fieldType->FieldHolder() . "</div>" .
            "<div class=\"fieldgroupField\">" . $this->fieldLink->FieldHolder() . "</div>" .
            "</div>";
    }

    public function setForm($form)
    {
        parent::setForm($form);

        if (isset($this->fileField)) $this->fileField->setForm($form);
        if (isset($this->internalField)) $this->internalField->setForm($form);

    }

    /**
     *
     */
    public function saveInto(DataObjectInterface $dataObject)
    {
        $fieldName = $this->name;
        $dataObjectField = $this->dataObjectField;
        if ($dataObject->hasMethod("set$fieldName")) {
            $fields = array(
                "Type" => $this->fieldType->Value(),
                "Internal" => $this->internalField->Value(),
                "External" => $this->externalField->Value(),
                "Email" => $this->emailField->Value(),
                "File" => $this->fileField->Value(),
                "Anchor" => $this->anchorField->Value(),
                "TargetBlank" => $this->targetBlankField->Value(),
                "Extra" => $this->extraField->Value()
            );
            if ($dataObjectField) {
                $fields['DataObject'] = $this->dataObjectField->Value();
            }
            $dataObject->$fieldName = DBField::create_field('WTLink', $fields);
        } else {
            if (!empty($dataObject->$fieldName)) {
                $dataObject->$fieldName->setType($this->fieldType->Value());
                $dataObject->$fieldName->setInternal($this->internalField->Value());
                $dataObject->$fieldName->setExternal($this->externalField->Value());
                $dataObject->$fieldName->setEmail($this->emailField->Value());
                $dataObject->$fieldName->setFile($this->fileField->Value());
                $dataObject->$fieldName->setAnchor($this->anchorField->Value());
                $dataObject->$fieldName->setTargetBlank($this->targetBlankField->Value());
                $dataObject->$fieldName->setExtra($this->extraField->Value());
                if ($dataObjectField) {
                    $dataObject->$fieldName->setDataObject($this->dataObjectField->Value());
                }
            }
        }
    }

    public function setValue($val)
    {
        $this->value = $val;
        $dataObjectField = $this->dataObjectField;
        if (is_array($val)) {
            $this->fieldType->setValue($val['Type']);
            $this->internalField->setValue($val['Internal']);
            $this->externalField->setValue($val['External']);
            $this->emailField->setValue($val['Email']);
            $this->fileField->setValue($val['File']);
            $this->anchorField->setValue($val['Anchor']);
            $this->targetBlankField->setValue(isset($val['TargetBlank']) ? $val['TargetBlank'] : false);
            $this->extraField->setValue($val['Extra']);
            if ($dataObjectField) {
                $this->dataObjectField->setValue($val['DataObject']);
            }
        } elseif ($val instanceof WTLink) {
            $this->fieldType->setValue($val->getType());
            $this->internalField->setValue($val->getInternal());
            $this->externalField->setValue($val->getExternal());
            $this->emailField->setValue($val->getEmail());
            $this->fileField->setValue($val->getFile());
            $this->anchorField->setValue($val->getAnchor());
            $this->targetBlankField->setValue($val->getTargetBlank());
            $this->extraField->setValue($val->getExtra());
            if ($dataObjectField) {
                $this->dataObjectField->setValue($val->getDataObject());
            }
        }

        return $this;
    }

}