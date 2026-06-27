<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class InquiryBox extends ObjectModel
{
    public $id_inquiry;
    public $email;
    public $title;
    public $content;
    public $approved;
    public $admin_reply;
    public $date_add;
    public $date_upd;
    public $id_employee;
    public $id_category;

    public static $definition = [
        'table' => 'inquiry',
        'primary' => 'id_inquiry',
        'fields' => [
            'email' => ['type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => true, 'size' => 255],
            'title' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 255],
            'content' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'required' => true],
            'approved' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'admin_reply' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'],
            'id_employee' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'id_category' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];
}
