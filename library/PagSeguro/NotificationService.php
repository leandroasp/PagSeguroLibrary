<?php if (!defined('ALLOW_PAGSEGURO_CONFIG')) { die('No direct script access allowed'); }
/*
************************************************************************
Copyright [2011] [PagSeguro Internet Ltda.]

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
************************************************************************
*/

/**
 * Encapsulates web service calls regarding PagSeguro notifications
 */
class PagSeguro_NotificationService {
	
	const serviceName = 'notificationService';
	
	private static function buildTransactionNotificationUrl(PagSeguro_ConnectionData $connectionData, $notificationCode) {
		$url   = $connectionData->getServiceUrl();
		return "{$url}/{$notificationCode}/?".$connectionData->getCredentialsUrlQuery();
	}
	
	/**
	 * Returns a transaction from a notification code
	 * 
	 * @param PagSeguroCredentials $credentials
	 * @param String $notificationCode
	 * @throws PagSeguroServiceException
	 * @throws Exception
	 * @return a transaction
	 * @see PagSeguroTransaction
	 */
	public static function checkTransaction(PagSeguro_Credentials $credentials, $notificationCode) {
		
		PagSeguro_Log::info("PagSeguro_NotificationService.CheckTransaction(notificationCode=$notificationCode) - begin");
		$connectionData = new PagSeguro_ConnectionData($credentials, self::serviceName);
		
		try {
			
			$connection = new PagSeguro_HttpConnection();
			$connection->get(
				self::buildTransactionNotificationUrl($connectionData, $notificationCode), // URL + par�metros de busca
				$connectionData->getServiceTimeout(), // Timeout
				$connectionData->getCharset() // charset
			);
			
			$httpStatus = new PagSeguro_HttpStatus($connection->getStatus());
			
			switch ($httpStatus->getType()) {
				
				case 'OK':
					// parses the transaction
					$transaction = PagSeguro_TransactionParser::readTransaction($connection->getResponse());
					PagSeguro_Log::info("PagSeguro_NotificationService.CheckTransaction(notificationCode=$notificationCode) - end ". $transaction->toString().")");
					break;
				
				case 'BAD_REQUEST':
					$errors = PagSeguro_TransactionParser::readErrors($connection->getResponse());
					$e = new PagSeguro_ServiceException($httpStatus, $errors);
					PagSeguro_Log::info("PagSeguro_NotificationService.CheckTransaction(notificationCode=$notificationCode) - error ".$e->getOneLineMessage());
					throw $e;
					break;
					
				default:
					$e = new PagSeguro_ServiceException($httpStatus);
					PagSeguro_Log::info("PagSeguro_NotificationService.CheckTransaction(notificationCode=$notificationCode) - error ".$e->getOneLineMessage());
					throw $e;
					break;
					
			}
			
			return isset($transaction) ? $transaction : null;
			
		} catch (PagSeguro_ServiceException $e) {
			throw $e;
		} catch (Exception $e) {
			PagSeguro_Log::error("Exception: ".$e->getMessage());
			throw $e;
		}
		
	}


}
	
?>