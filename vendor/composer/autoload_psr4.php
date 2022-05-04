<?php

// autoload_psr4.php @generated by Composer

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'tests\\' => array($baseDir . '/tests'),
    'phpDocumentor\\Reflection\\' => array($vendorDir . '/phpdocumentor/reflection-common/src', $vendorDir . '/phpdocumentor/reflection-docblock/src', $vendorDir . '/phpdocumentor/type-resolver/src'),
    'models\\' => array($baseDir . '/modelos', $vendorDir . '/gamboa.martin/test/src/models'),
    'gamboamartin\\test\\' => array($vendorDir . '/gamboa.martin/errores/tests', $vendorDir . '/gamboa.martin/test/src'),
    'gamboamartin\\errores\\' => array($vendorDir . '/gamboa.martin/errores/src'),
    'controllers\\' => array($vendorDir . '/gamboa.martin/test/src/controllers'),
    'config\\' => array($baseDir . '/config'),
    'Webmozart\\Assert\\' => array($vendorDir . '/webmozart/assert/src'),
    'Symfony\\Polyfill\\Ctype\\' => array($vendorDir . '/symfony/polyfill-ctype'),
    'Prophecy\\' => array($vendorDir . '/phpspec/prophecy/src/Prophecy'),
    'PhpParser\\' => array($vendorDir . '/nikic/php-parser/lib/PhpParser'),
    'Fpdf\\' => array($vendorDir . '/fpdf/fpdf/src/Fpdf'),
    'Doctrine\\Instantiator\\' => array($vendorDir . '/doctrine/instantiator/src/Doctrine/Instantiator'),
    'DeepCopy\\' => array($vendorDir . '/myclabs/deep-copy/src/DeepCopy'),
);
