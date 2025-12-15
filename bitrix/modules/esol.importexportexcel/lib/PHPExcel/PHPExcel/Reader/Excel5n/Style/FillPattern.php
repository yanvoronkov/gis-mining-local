<?php

class KDAPHPExcel_Reader_Excel5n_Style_FillPattern
{
    protected static $map = array(
        0x00 => KDAPHPExcel_Style_Fill::FILL_NONE,
        0x01 => KDAPHPExcel_Style_Fill::FILL_SOLID,
        0x02 => KDAPHPExcel_Style_Fill::FILL_PATTERN_MEDIUMGRAY,
        0x03 => KDAPHPExcel_Style_Fill::FILL_PATTERN_DARKGRAY,
        0x04 => KDAPHPExcel_Style_Fill::FILL_PATTERN_LIGHTGRAY,
        0x05 => KDAPHPExcel_Style_Fill::FILL_PATTERN_DARKHORIZONTAL,
        0x06 => KDAPHPExcel_Style_Fill::FILL_PATTERN_DARKVERTICAL,
        0x07 => KDAPHPExcel_Style_Fill::FILL_PATTERN_DARKDOWN,
        0x08 => KDAPHPExcel_Style_Fill::FILL_PATTERN_DARKUP,
        0x09 => KDAPHPExcel_Style_Fill::FILL_PATTERN_DARKGRID,
        0x0A => KDAPHPExcel_Style_Fill::FILL_PATTERN_DARKTRELLIS,
        0x0B => KDAPHPExcel_Style_Fill::FILL_PATTERN_LIGHTHORIZONTAL,
        0x0C => KDAPHPExcel_Style_Fill::FILL_PATTERN_LIGHTVERTICAL,
        0x0D => KDAPHPExcel_Style_Fill::FILL_PATTERN_LIGHTDOWN,
        0x0E => KDAPHPExcel_Style_Fill::FILL_PATTERN_LIGHTUP,
        0x0F => KDAPHPExcel_Style_Fill::FILL_PATTERN_LIGHTGRID,
        0x10 => KDAPHPExcel_Style_Fill::FILL_PATTERN_LIGHTTRELLIS,
        0x11 => KDAPHPExcel_Style_Fill::FILL_PATTERN_GRAY125,
        0x12 => KDAPHPExcel_Style_Fill::FILL_PATTERN_GRAY0625,
    );

    /**
     * Get fill pattern from index
     * OpenOffice documentation: 2.5.12
     *
     * @param int $index
     * @return string
     */
    public static function lookup($index)
    {
        if (isset(self::$map[$index])) {
            return self::$map[$index];
        }
        return KDAPHPExcel_Style_Fill::FILL_NONE;
    }
}