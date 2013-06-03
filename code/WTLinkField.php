<?php

class WTLinkField extends TextField {


        public static $url_handlers = array(
                '$Action!/$ID' => '$Action'
        );

        public static $allowed_actions = array(
                'tree', 'InternalTree', 'FileTree'
        );

        protected $fieldField = null, $internalField = null, $externalField = null, $emailField = null, $fileField = null, $anchorField = null, $targetBlankField = null;
        /**
         * @var FormField
         */
        protected $fieldType = null;

        /**
         * @var FormField
         */
        protected $fieldLink = null;

        function __construct($name, $title = null, $value = "") {
                // naming with underscores to prevent values from actually being saved somewhere
                $this->fieldType =new OptionsetField(
                        "{$name}[Type]",
                        _t('HtmlEditorField.LINKTO', 'Link to'),
                        array(
                                'Internal' => _t('HtmlEditorField.LINKINTERNAL', 'Page on the site'),
                                'External' => _t('HtmlEditorField.LINKEXTERNAL', 'Another website'),
                                //'Anchor' => _t('HtmlEditorField.LINKANCHOR', 'Anchor on this page'),
                                'Email' => _t('HtmlEditorField.LINKEMAIL', 'Email address'),
                                'File' => _t('HtmlEditorField.LINKFILE', 'Download a file'),
                        ),
                        'Internal'
                );
                $this->fieldLink = new CompositeField(
                        $this->internalField = new WTTreeDropdownField("{$name}[Internal]", _t('HtmlEditorField.Internal', 'Internal'), 'SiteTree', 'ID', 'Title', true),
                        $this->externalField = new TextField("{$name}[External]", _t('HtmlEditorField.URL', 'URL'), 'http://'),
                        $this->emailField = new EmailField("{$name}[Email]", _t('HtmlEditorField.EMAIL', 'Email address')),
                        $this->fileField = new WTTreeDropdownField("{$name}[File]", _t('HtmlEditorField.FILE', 'File'), 'File', 'ID', 'Title', true),
                        $this->anchorField = new TextField("{$name}[Anchor]", 'Anchor (optional)'),
                        $this->targetBlankField = new CheckboxField("{$name}[TargetBlank]", _t('HtmlEditorField.LINKOPENNEWWIN', 'Open link in a new window?'))
                );/*new TextField("{$name}[Link]", 'Link');*/

                $this->anchorField->addExtraClass('no-hide');
                $this->targetBlankField->addExtraClass('no-hide');

                parent::__construct($name, $title, $value);
        }

        public function FileTree(SS_HTTPRequest $request) {
                return $this->fileField->tree($request);
        }

        public function InternalTree(SS_HTTPRequest $request) {
                return $this->internalField->tree($request);
        }

        /**
         * @return string
         */
        function Field($properties = array()) {
                Requirements::javascript(LINK_FIELD_DIR . '/javascript/admin/WTLinkField.js');
                return "<div class=\"fieldgroup\">" .
                        "<div class=\"fieldgroupField\">" . $this->fieldType->FieldHolder() . "</div>" .
                        "<div class=\"fieldgroupField\">" . $this->fieldLink->FieldHolder() . "</div>" .
                        "</div>";
        }

        public function setForm($form) {
                parent::setForm($form);

                if (isset($this->fileField)) $this->fileField->setForm($form);
                if (isset($this->internalField)) $this->internalField->setForm($form);

        }

        /**
         * 30/06/2009 - Enhancement:
         * SaveInto checks if set-methods are available and use them
         * instead of setting the values in the money class directly. saveInto
         * initiates a new Money class object to pass through the values to the setter
         * method.
         *
         */
        function saveInto(DataObjectInterface $dataObject) {
                $fieldName = $this->name;
                if($dataObject->hasMethod("set$fieldName")) {
                        $dataObject->$fieldName = DBField::create_field('WTLink', array(
                                "Type" => $this->fieldType->Value(),
                                "Internal" => $this->internalField->Value(),
                                "External" => $this->externalField->Value(),
                                "Email" => $this->emailField->Value(),
                                "File" => $this->fileField->Value(),
                                "TargetBlank" => $this->targetBlankField->Value()
                        ));
                } else {
                        if (!empty($dataObject->$fieldName)) {
                                $dataObject->$fieldName->setType($this->fieldType->Value());
                                $dataObject->$fieldName->setInternal($this->internalField->Value());
                                $dataObject->$fieldName->setExternal($this->externalField->Value());
                                $dataObject->$fieldName->setEmail($this->emailField->Value());
                                $dataObject->$fieldName->setFile($this->fileField->Value());
                                $dataObject->$fieldName->setTargetBlank($this->targetBlankField->Value());
                        }
                }
        }

        function setValue($val) {
                $this->value = $val;

                if(is_array($val)) {
                        $this->fieldType->setValue($val['Type']);
                        $this->internalField->setValue($val['Internal']);
                        $this->externalField->setValue($val['External']);
                        $this->emailField->setValue($val['Email']);
                        $this->fileField->setValue($val['File']);
                        $this->targetBlankField->setValue(isset($val['TargetBlank']) ? $val['TargetBlank'] : false);
                } elseif($val instanceof WTLink) {
                        $this->fieldType->setValue($val->getType());
                        $this->internalField->setValue($val->getInternal());
                        $this->externalField->setValue($val->getExternal());
                        $this->emailField->setValue($val->getEmail());
                        $this->fileField->setValue($val->getFile());
                        $this->targetBlankField->setValue($val->getTargetBlank());
                }

                // @todo Format numbers according to current locale, incl.
                //  decimal and thousands signs, while respecting the stored
                //  precision in the database without truncating it during display
                //  and subsequent save operations

                return $this;
        }

}


class WTLink extends DBField implements CompositeDBField {


        /**
         * @var string $getCurrency()
         */
        protected $type, $internal, $external, $email, $file, $targetBlank;

        /**
         * @var float $currencyAmount
         */
        protected $link;

        protected $isChanged = false;

        /**
         * @param array
         */
        static $composite_db = array(
                "Type" => "Enum('Internal, External, Email, File', 'Internal')",
                "Internal" => 'Int',
                "External" => 'Varchar(255)',
                "Email" => 'Varchar(255)',
                "File" => 'Int',
                'TargetBlank' => 'Boolean'
        );

        function compositeDatabaseFields() {
                return self::$composite_db;
        }


        function __construct($name = null) {
                parent::__construct($name);
        }


        function isChanged() {
                return $this->isChanged;
        }

        function setValue($value, $record = null, $markChanged = true) {
                if ($value instanceof WTLink && $value->exists()) {
                        $this->setType($value->getType(), $markChanged);
                        $this->setInternal($value->getInternal(), $markChanged);
                        $this->setExternal($value->getExternal(), $markChanged);
                        $this->setEmail($value->getEmail(), $markChanged);
                        $this->setFile($value->getFile(), $markChanged);
                        $this->setTargetBlank($value->getTargetBlank(), $markChanged);

                        if($markChanged) $this->isChanged = true;
                } else if($record && isset($record[$this->name . 'Type'])) {
                        if($record[$this->name . 'Type']) {
                                if(!empty($record[$this->name . 'Type'])) $this->setType($record[$this->name . 'Type'], $markChanged);
                                else $this->setType('internal', $markChanged);

                                if (isset($record[$this->name . 'Internal'])) $this->setInternal($record[$this->name . 'Internal'], $markChanged);
                                if (isset($record[$this->name . 'External'])) $this->setExternal($record[$this->name . 'External'], $markChanged);
                                if (isset($record[$this->name . 'Email'])) $this->setEmail($record[$this->name . 'Email'], $markChanged);
                                if (isset($record[$this->name . 'File'])) $this->setFile($record[$this->name . 'File'], $markChanged);
                                if (isset($record[$this->name . 'TargetBlank']))  $this->setTargetBlank($record[$this->name . 'TargetBlank'], $markChanged);
                        } else {
                                $this->value = $this->nullValue();
                        }
                        if($markChanged) $this->isChanged = true;
                } else if (is_array($value)) {
                        if (array_key_exists('Type', $value)) {
                                $this->setType($value['Type'], $markChanged);
                        }
                        if (array_key_exists('Internal', $value)) {
                                $this->setInternal($value['Internal'], $markChanged);
                        }
                        if (array_key_exists('Email', $value)) {
                                $this->setEmail($value['Email'], $markChanged);
                        }
                        if (array_key_exists('File', $value)) {
                                $this->setFile($value['File'], $markChanged);
                        }
                        if (array_key_exists('External', $value)) {
                                $this->setExternal($value['External'], $markChanged);
                        }
                        if (array_key_exists('TargetBlank', $value)) {
                                $this->setTargetBlank($value['TargetBlank'], $markChanged);
                        }
                        if($markChanged) $this->isChanged = true;
                } else {
                        // @todo Allow to reset a money value by passing in NULL
                        //user_error('Invalid value in Money->setValue()', E_USER_ERROR);
                }
        }

        function requireField() {
                $fields = $this->compositeDatabaseFields();
                if($fields) foreach($fields as $name => $type){
                        DB::requireField($this->tableName, $this->name.$name, $type);
                }
        }

        public function setType($value, $markChanged = true) {
                $this->type = $value;
                if($markChanged) $this->isChanged = true;
        }

        public function getType() {
                return $this->type;
        }

        public function setInternal($value, $markChanged = true) {
                $this->internal = $value;
                if($markChanged) $this->isChanged = true;
        }

        public function getInternal() {
                return $this->internal;
        }


        public function getExternal() {
                return $this->external;
        }

        public function setExternal($value, $markChanged = true) {
                if ($value && (stripos($value, 'http://') === false && stripos($value, 'https://') === false)) $value = 'http://' . $value;
                $this->external = $value;
                if($markChanged) $this->isChanged = true;
        }

        public function getEmail() {
                return $this->email;
        }

        public function setEmail($value, $markChanged = true) {
                $this->email = $value;
                if($markChanged) $this->isChanged = true;
        }


        public function setFile($value, $markChanged = true) {
                $this->file = $value;
                if($markChanged) $this->isChanged = true;
        }

        public function getFile() {
                return $this->file;
        }

        public function setTargetBlank($value, $markChanged = true) {
                $this->targetBlank = $value;
                if($markChanged) $this->isChanged = true;
        }

        public function getTargetBlank() {
                return $this->targetBlank;
        }

        function exists() {
                return ($this->getType());
        }

        /**
         * Returns a CompositeField instance used as a default
         * for form scaffolding.
         *
         * Used by {@link SearchContext}, {@link ModelAdmin}, {@link DataObject::scaffoldFormFields()}
         *
         * @param string $title Optional. Localized title of the generated instance
         * @return FormField
         */
        public function scaffoldFormField($title = null) {
                $field = new WTLinkField($this->name);
                return $field;
        }

        /**
         * For backwards compatibility reasons
         * (mainly with ecommerce module),
         * this returns the amount value of the field,
         * rather than a {@link Nice()} formatting.
         */
        function __toString() {
                return (string)$this->getAmount();
        }

        function writeToManipulation(&$manipulation) {
                if($this->getType()) {
                        $manipulation['fields'][$this->name.'Type'] = $this->prepValueForDB($this->getType());
                } else {
                        $manipulation['fields'][$this->name.'Type'] = DBField::create_field('Varchar', $this->getType())->nullValue();
                }

                if($this->getInternal()) {
                        $manipulation['fields'][$this->name.'Internal'] = $this->getInternal();
                } else {
                        $manipulation['fields'][$this->name.'Internal'] = DBField::create_field('Int', $this->getInternal())->nullValue();
                }

                if($this->getExternal()) {
                        $manipulation['fields'][$this->name.'External'] = $this->prepValueForDB($this->getExternal());
                } else {
                        $manipulation['fields'][$this->name.'External'] = DBField::create_field('Varchar', $this->getExternal())->nullValue();
                }

                if($this->getEmail()) {
                        $manipulation['fields'][$this->name.'Email'] = $this->prepValueForDB($this->getEmail());
                } else {
                        $manipulation['fields'][$this->name.'Email'] = DBField::create_field('Varchar', $this->getEmail())->nullValue();
                }

                if($this->getFile()) {
                        $manipulation['fields'][$this->name.'File'] = $this->getFile();
                } else {
                        $manipulation['fields'][$this->name.'File'] = DBField::create_field('Int', $this->getFile())->nullValue();
                }

                if($this->getTargetBlank()) {
                        $manipulation['fields'][$this->name.'TargetBlank'] = $this->getTargetBlank();
                } else {
                        $manipulation['fields'][$this->name.'TargetBlank'] = DBField::create_field('Int', $this->getTargetBlank())->nullValue();
                }
        }

        public function addToQuery(&$query) {
                //parent::addToQuery($query);
                $query->selectField(sprintf('"%sType"', $this->name));
                $query->selectField(sprintf('"%sInternal"', $this->name));
                $query->selectField(sprintf('"%sExternal"', $this->name));
                $query->selectField(sprintf('"%sEmail"', $this->name));
                $query->selectField(sprintf('"%sFile"', $this->name));
                $query->selectField(sprintf('"%sTargetBlank"', $this->name));
        }

        public function Link() {
                $link = '';
                switch($this->type) {
                        case 'Internal' :
                                if ($this->internal) $link = SiteTree::get()->byID($this->internal)->Link();
                                break;
                        case 'External' :
                                $link = $this->external;
                                break;
                        case 'Email' :
                                $link = $this->email ? 'mailto:' . $this->email : '';
                                break;
                        case 'File' :
                                if ($this->file) $link = File::get()->byID($this->file)->Filename;
                }

                return $link;
        }

        public function Tag() {
                $link = $this->Link();

                if ($link) {
                        $target = !empty($this->targetBlank) ? 'target="_blank"' : '';

                        return "<a href=\"{$link}\" {$target}>";
                }

                return '';
        }
}
