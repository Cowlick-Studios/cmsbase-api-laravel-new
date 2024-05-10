<?php

return [

  // List of all custom types and corresponding SQL type
  'collection_types' => [
    
    // integer
    "tinyInteger" => "tinyInteger",
    "unsignedTinyInteger" => "unsignedTinyInteger",
    "smallInteger" => "smallInteger",
    "unsignedSmallInteger" => "unsignedSmallInteger",
    "integer" => "integer",
    "unsignedInteger" => "unsignedInteger",
    "mediumInteger" => "mediumInteger",
    "unsignedMediumInteger" => "unsignedMediumInteger",
    "bigInteger" => "bigInteger",
    "unsignedBigInteger" => "unsignedBigInteger",

    // float
    "decimal" => "decimal",
    "unsignedDecimal" => "unsignedDecimal",
    "float" => "float",
    "double" => "double",

    // text
    "char" => "char",
    "string" => "string",
    "tinyText" => "tinyText",
    "text" => "text",
    "mediumText" => "mediumText",
    "longText" => "longText",

    // other
    "boolean" => "boolean",
    "date" => "date",
    "time" => "time",
    "dateTime" => "dateTime",
    "timestamp" => "timestamp",

    // custom
    "richText" => "longText",
    "file" => "unsignedBigInteger"
  ],

  'default_settings' => [
    'request_logging' => false,
    'client_request_logging' => false,
    'public_auth_register' => false
  ]
];
