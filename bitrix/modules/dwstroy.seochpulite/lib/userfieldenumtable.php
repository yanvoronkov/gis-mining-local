<?php
namespace Dwstroy\SeoChpuLite;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\BooleanField,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\StringField,
    Bitrix\Main\ORM\Fields\Validators\LengthValidator,
    Bitrix\Main\Entity\ReferenceField;

Loc::loadMessages(__FILE__);

/**
 * Class FieldEnumTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_FIELD_ID int optional
 * <li> VALUE string(255) mandatory
 * <li> DEF bool ('N', 'Y') optional default 'N'
 * <li> SORT int optional default 500
 * <li> XML_ID string(255) mandatory
 * </ul>
 *
 * @package Bitrix\User
 **/

class UserFieldEnumTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_user_field_enum';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            new IntegerField(
                'ID',
                [
                    'primary' => true,
                    'autocomplete' => true,
                    'title' => Loc::getMessage('FIELD_ENUM_ENTITY_ID_FIELD')
                ]
            ),
            new IntegerField(
                'USER_FIELD_ID',
                [
                    'title' => Loc::getMessage('FIELD_ENUM_ENTITY_USER_FIELD_ID_FIELD')
                ]
            ),
            'USER_FIELD' => new ReferenceField(
                'USER_FIELD_ID',
                'Bitrix\Main\UserFieldTable',
                array('=this.USER_FIELD_ID' => 'ref.ID'),
                array(
                    'title' => Loc::getMessage('IBLOCK_SECTION_ELEMENT_ENTITY_IBLOCK_ELEMENT_FIELD'),
                )
            ),
            new StringField(
                'VALUE',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateValue'],
                    'title' => Loc::getMessage('FIELD_ENUM_ENTITY_VALUE_FIELD')
                ]
            ),
            new BooleanField(
                'DEF',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'N',
                    'title' => Loc::getMessage('FIELD_ENUM_ENTITY_DEF_FIELD')
                ]
            ),
            new IntegerField(
                'SORT',
                [
                    'default' => 500,
                    'title' => Loc::getMessage('FIELD_ENUM_ENTITY_SORT_FIELD')
                ]
            ),
            new StringField(
                'XML_ID',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateXmlId'],
                    'title' => Loc::getMessage('FIELD_ENUM_ENTITY_XML_ID_FIELD')
                ]
            ),
        ];
    }

    /**
     * Returns validators for VALUE field.
     *
     * @return array
     */
    public static function validateValue()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }

    /**
     * Returns validators for XML_ID field.
     *
     * @return array
     */
    public static function validateXmlId()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }
}