<?php if (!defined('ALLOW_PAGSEGURO_CONFIG')) { die('No direct script access allowed'); }
/*
************************************************************************
PagSeguro Config File
************************************************************************
*/

$PagSeguroConfig = array();

$PagSeguroConfig['environment'] = Array();
$PagSeguroConfig['environment']['environment'] = "production";

$PagSeguroConfig['credentials'] = Array();
$PagSeguroConfig['credentials']['email'] = "seu@email.com.br";
$PagSeguroConfig['credentials']['token'] = "AQUI_SEU_TOKEN";

$PagSeguroConfig['application'] = Array();
$PagSeguroConfig['application']['charset'] = "ISO-8859-1"; // UTF-8

defined('PAGSEGURO_LOG') || define('PAGSEGURO_LOG', FALSE);

$PagSeguroConfig['log'] = Array();
$PagSeguroConfig['log']['active'] = PAGSEGURO_LOG;

//Aqui um local para seu log
$PagSeguroConfig['log']['fileLocation'] = realpath(APPLICATION_PATH . '/../data') . '/pagseguro_api.log';
