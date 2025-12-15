<?php

class KDAPHPExcel_Reader_Excel5n_Style_Border
{
    protected static $map = array(
        0x00 => KDAPHPExcel_Style_Border::BORDER_NONE,
        0x01 => KDAPHPExcel_Style_Border::BORDER_THIN,
        0x02 => KDAPHPExcel_Style_Border::BORDER_MEDIUM,
        0x03 => KDAPHPExcel_Style_Border::BORDER_DASHED,
        0x04 => KDAPHPExcel_Style_Border::BORDER_DOTTED,
        0x05 => KDAPHPExcel_Style_Border::BORDER_THICK,
        0x06 => KDAPHPExcel_Style_Border::BORDER_DOUBLE,
        0x07 => KDAPHPExcel_Style_Border::BORDER_HAIR,
        0x08 => KDAPHPExcel_Style_Border::BORDER_MEDIUMDASHED,
        0x09 => KDAPHPExcel_Style_Border::BORDER_DASHDOT,
        0x0A => KDAPHPExcel_Style_Border::BORDER_MEDIUMDASHDOT,
        0x0B => KDAPHPExcel_Style_Border::BORDER_DASHDOTDOT,
        0x0C => KDAPHPExcel_Style_Border::BORDER_MEDIUMDASHDOTDOT,
        0x0D => KDAPHPExcel_Style_Border::BORDER_SLANTDASHDOT,
    );

    /**
     * Map border style
     * OpenOffice documentation: 2.5.11
     *
     * @param int $index
     * @return string
     */
    public static function lookup($index)
    {
        if (isset(self::$map[$index])) {
            return self::$map[$index];
        }
        return KDAPHPExcel_Style_Border::BORDER_NONE;
    }
}