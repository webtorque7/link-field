<?php

class WTLink extends DBField implements CompositeDBField
{
    protected $type, $internal, $external, $email, $file, $targetBlank, $anchor, $extra, $dataObject;

    protected $link;

    protected $isChanged = false;

    /**
     * @param array
     */
    private static $composite_db = array(
        "Type" => "Enum('Internal, External, Email, File, DataObject', 'Internal')",
        "Internal" => 'Varchar',
        "External" => 'Varchar(255)',
        "Extra" => 'Varchar(255)',
        "Email" => 'Varchar(255)',
        "File" => 'Varchar',
        'TargetBlank' => 'Varchar',
        'DataObject' => 'Varchar(255)',
        'Anchor' => 'Varchar(100)'
    );

    public function compositeDatabaseFields()
    {
        return self::$composite_db;
    }

    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    public function isChanged()
    {
        return $this->isChanged;
    }

    public function setValue($value, $record = null, $markChanged = true)
    {
        if ($value instanceof WTLink && $value->exists()) {
            $this->setType($value->getType(), $markChanged);
            $this->setInternal($value->getInternal(), $markChanged);
            $this->setExternal($value->getExternal(), $markChanged);
            $this->setEmail($value->getEmail(), $markChanged);
            $this->setFile($value->getFile(), $markChanged);
            $this->setTargetBlank($value->getTargetBlank(), $markChanged);
            $this->setAnchor($value->getAnchor(), $markChanged);
            $this->setExtra($value->getExtra(), $markChanged);
            $this->setDataObject($value->getDataObject(), $markChanged);

            if ($markChanged) {
                $this->isChanged = true;
            }
        } else if ($record && isset($record[$this->name . 'Type'])) {
            if ($record[$this->name . 'Type']) {
                if (!empty($record[$this->name . 'Type'])) {
                    $this->setType($record[$this->name . 'Type'], $markChanged);
                } else {
                    $this->setType('internal', $markChanged);
                }

                if (isset($record[$this->name . 'Internal'])) {
                    $this->setInternal($record[$this->name . 'Internal'], $markChanged);
                }
                if (isset($record[$this->name . 'External'])) {
                    $this->setExternal($record[$this->name . 'External'], $markChanged);
                }
                if (isset($record[$this->name . 'Email'])) {
                    $this->setEmail($record[$this->name . 'Email'], $markChanged);
                }
                if (isset($record[$this->name . 'File'])) {
                    $this->setFile($record[$this->name . 'File'], $markChanged);
                }
                if (isset($record[$this->name . 'TargetBlank'])) {
                    $this->setTargetBlank($record[$this->name . 'TargetBlank'], $markChanged);
                }
                if (isset($record[$this->name . 'Anchor'])) {
                    $this->setAnchor($record[$this->name . 'Anchor'], $markChanged);
                }
                if (isset($record[$this->name . 'Extra'])) {
                    $this->setExtra($record[$this->name . 'Extra'], $markChanged);
                }
                if (isset($record[$this->name . 'DataObject'])) {
                    $this->setDataObject($record[$this->name . 'DataObject'], $markChanged);
                }
            } else {
                $this->value = $this->nullValue();
            }
            if ($markChanged) {
                $this->isChanged = true;
            }
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
            if (array_key_exists('Anchor', $value)) {
                $this->setAnchor($value['Anchor'], $markChanged);
            }
            if (array_key_exists('Extra', $value)) {
                $this->setExtra($value['Extra'], $markChanged);
            }
            if (array_key_exists('DataObject', $value)) {
                $this->setDataObject($value['DataObject'], $markChanged);
            }
            if ($markChanged) {
                $this->isChanged = true;
            }
        } else {

        }
    }

    public function requireField()
    {
        $fields = $this->compositeDatabaseFields();
        if ($fields) {
            foreach ($fields as $name => $type) {
                DB::require_field($this->tableName, $this->name . $name, $type);
            }
        }
    }

    public function setType($value, $markChanged = true)
    {
        $this->type = $value;
        if ($markChanged) {
            $this->isChanged = true;
        }
    }

    public function getType()
    {
        return $this->type;
    }

    public function setInternal($value, $markChanged = true)
    {
        $this->internal = $value;
        if ($markChanged) {
            $this->isChanged = true;
        }
    }

    public function getInternal()
    {
        return (int)$this->internal;
    }


    public function getExtra()
    {
        return $this->extra;
    }

    public function setExtra($v, $markChanged = true)
    {
        $this->extra = $v;

        if ($markChanged) {
            $this->isChanged = true;
        }

        return $this;
    }

    public function getDataObject()
    {
        return $this->dataObject;
    }

    public function setDataObject($v, $markChanged = true)
    {
        $this->dataObject = $v;

        if ($markChanged) {
            $this->isChanged = true;
        }

        return $this;
    }

    public function getExternal()
    {
        return $this->external;
    }

    public function setExternal($value, $markChanged = true)
    {
        if ($value && (stripos($value, 'http://') === false && stripos($value, 'https://') === false)) {
            $value = 'http://' . $value;
        }
        $this->external = $value;
        if ($markChanged) {
            $this->isChanged = true;
        }
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($value, $markChanged = true)
    {
        $this->email = $value;
        if ($markChanged) {
            $this->isChanged = true;
        }
    }


    public function setFile($value, $markChanged = true)
    {
        $this->file = $value;
        if ($markChanged) {
            $this->isChanged = true;
        }
    }

    public function getFile()
    {
        return (int)$this->file;
    }

    public function setTargetBlank($value, $markChanged = true)
    {
        $this->targetBlank = $value;
        if ($markChanged) {
            $this->isChanged = true;
        }
    }

    public function getTargetBlank()
    {
        return (int)$this->targetBlank;
    }

    public function setAnchor($value, $markChanged = true)
    {
        $this->anchor = $value;
        if ($markChanged) {
            $this->isChanged = true;
        }
    }

    public function getAnchor()
    {
        return $this->anchor;
    }


    public function exists()
    {
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
    public function scaffoldFormField($title = null)
    {
        $field = new WTLinkField($this->name);
        return $field;
    }

    public function writeToManipulation(&$manipulation)
    {
        if ($this->getType()) {
            $manipulation['fields'][$this->name . 'Type'] = $this->prepValueForDB($this->getType());
        } else {
            $manipulation['fields'][$this->name . 'Type'] = DBField::create_field(
                'Varchar',
                $this->getType()
            )->nullValue();
        }

        if ($this->getInternal()) {
            $manipulation['fields'][$this->name . 'Internal'] = $this->getInternal();
        } else {
            $manipulation['fields'][$this->name . 'Internal'] = DBField::create_field(
                'Int',
                $this->getInternal()
            )->nullValue();
        }

        if ($this->getExternal()) {
            $manipulation['fields'][$this->name . 'External'] = $this->prepValueForDB($this->getExternal());
        } else {
            $manipulation['fields'][$this->name . 'External'] = DBField::create_field(
                'Varchar',
                $this->getExternal()
            )->nullValue();
        }

        if ($this->getEmail()) {
            $manipulation['fields'][$this->name . 'Email'] = $this->prepValueForDB($this->getEmail());
        } else {
            $manipulation['fields'][$this->name . 'Email'] = DBField::create_field(
                'Varchar',
                $this->getEmail()
            )->nullValue();
        }

        if ($this->getFile()) {
            $manipulation['fields'][$this->name . 'File'] = $this->getFile();
        } else {
            $manipulation['fields'][$this->name . 'File'] = DBField::create_field('Int', $this->getFile())->nullValue();
        }

        if ($this->getTargetBlank()) {
            $manipulation['fields'][$this->name . 'TargetBlank'] = $this->getTargetBlank();
        } else {
            $manipulation['fields'][$this->name . 'TargetBlank'] = DBField::create_field(
                'Int',
                $this->getTargetBlank()
            )->nullValue();
        }

        if ($this->getAnchor()) {
            $manipulation['fields'][$this->name . 'Anchor'] = $this->prepValueForDB($this->getAnchor());
        } else {
            $manipulation['fields'][$this->name . 'Anchor'] = DBField::create_field(
                'Varchar',
                $this->getAnchor()
            )->nullValue();
        }

        if ($this->getExtra()) {
            $manipulation['fields'][$this->name . 'Extra'] = $this->prepValueForDB($this->getExtra());
        } else {
            $manipulation['fields'][$this->name . 'Extra'] = DBField::create_field(
                'Varchar',
                $this->getExtra()
            )->nullValue();
        }

        if ($this->getDataObject()) {
            $manipulation['fields'][$this->name . 'DataObject'] = $this->prepValueForDB($this->getDataObject());
        } else {
            $manipulation['fields'][$this->name . 'DataObject'] = DBField::create_field(
                'Varchar',
                $this->getDataObject()
            )->nullValue();
        }
    }

    public function addToQuery(&$query)
    {
        $query->selectField(sprintf('"%sType"', $this->name));
        $query->selectField(sprintf('"%sInternal"', $this->name));
        $query->selectField(sprintf('"%sExternal"', $this->name));
        $query->selectField(sprintf('"%sEmail"', $this->name));
        $query->selectField(sprintf('"%sFile"', $this->name));
        $query->selectField(sprintf('"%sTargetBlank"', $this->name));
        $query->selectField(sprintf('"%sAnchor"', $this->name));
        $query->selectField(sprintf('"%sExtra"', $this->name));
        $query->selectField(sprintf('"%sDataObject"', $this->name));
    }

    /**
     * Determines the link by the type and what is set
     *
     * @return mixed|string
     */
    public function Link()
    {
        $link = '';
        switch ($this->type) {
            case 'Internal' :
                if ($this->internal && ($page = SiteTree::get()->byID($this->internal))) {
                    $link = $page->Link();
                }
                break;
            case 'External' :
                $link = $this->external;
                break;
            case 'Email' :
                $link = $this->email ? 'mailto:' . $this->email : '';
                break;
            case 'File' :
                if ($this->file) {
                    $link = File::get()->byID($this->file)->Filename;
                }
                break;
            case 'DataObject':
                if ($do = $this->getDO()) {
                    $link = $do->Link();
                }
                break;
        }

        if ($this->extra) {
            //added a query string, but no ? to start it
            if (strpos($this->extra, '=' !== false && strpos($this->extra, '?' === false))) {
                $link .= '?';
            }

            $link .= $this->extra;
        }

        if ($this->anchor) {
            $link .= '#' . $this->anchor;
        }

        return $link;
    }

    /**
     * Creates the anchor tag, if $text is it is put inside the anchor tag, otherwise it only returns
     * the opening of the ancor tag
     *
     * @param string|null $text
     * @return string
     */
    public function Tag($text = null)
    {
        $link = $this->Link();

        if ($link) {
            $target = !empty($this->targetBlank) ? 'target="_blank"' : '';

            return $text ? "<a href=\"{$link}\" {$target}>" . Convert::raw2xml(
                    $text
                ) . '</a>' : "<a href=\"{$link}\" {$target}>";
        }

        return '';
    }

    /**
     * Get the DataObject instance
     *
     * @return DataObject|null
     */
    public function getDO()
    {
        if (!empty($this->dataObject)) {
            list($className, $ID) = explode('-', $this->dataObject);

            return DataList::create($className)->byID($ID);
        }

        return null;
    }

    /**
     * Get DataObject classes which can be used for linking, uses config WTLink.data_objects,
     * falls back to looking up classes which have implemented the WTLinkableInterface
     *
     * @return array|mixed
     */
    public static function get_data_object_types()
    {
        //first check if a list of data objects has been configured
        $configured = static::config()->data_objects;

        if ($configured) return $configured;

        //if no config, use classes which implement WTLinkableInterface
        return ClassInfo::implementorsOf(WTLinkableInterface::class);
    }

}