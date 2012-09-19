<?php if (!defined('PAGSEGURO_LIBRARY')) { die('No direct script access allowed'); }
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
 * Encapsulates web service calls to search for PagSeguro transactions
 */
class PagSeguro_TransactionSearchService {
	
	const serviceName = 'transactionSearchService';
	
	private static function buildSearchUrlByCode(PagSeguro_ConnectionData $connectionData, $transactionCode) {
		$url   = $connectionData->getServiceUrl();
		return  "{$url}/{$transactionCode}/?".$connectionData->getCredentialsUrlQuery();
    }
	
	private static function buildSearchUrlByDate(PagSeguro_ConnectionData $connectionData, Array $searchParams) {
		$url = $connectionData->getServiceUrl();
		$initialDate  = $searchParams['initialDate'] 	!= null ? $searchParams['initialDate'] : "";
		$finalDate    = $searchParams['finalDate'] 		!= null ? ("&finalDate=".$searchParams['finalDate']) : "";
		if ($searchParams['pageNumber'] != null) {
            $page = "&page=" . $searchParams['pageNumber'];
        }
		if ($searchParams['maxPageResults'] != null) {
            $maxPageResults = "&maxPageResults=" . $searchParams['maxPageResults'];
        }		
        return  "{$url}/?".$connectionData->getCredentialsUrlQuery()."&initialDate={$initialDate}{$finalDate}{$page}{$maxPageResults}";
    }
    
    private static function buildSearchUrlAbandoned(PagSeguro_ConnectionData $connectionData, Array $searchParams) {
    	$url = $connectionData->getServiceUrl();
		$initialDate  = $searchParams['initialDate'] 	!= null ? $searchParams['initialDate'] : "";
		$finalDate    = $searchParams['finalDate'] 		!= null ? ("&finalDate=".$searchParams['finalDate']) : "";
    	if ($searchParams['pageNumber'] != null) {
    		$page = "&page=" . $searchParams['pageNumber'];
    	}
    	if ($searchParams['maxPageResults'] != null) {
    		$maxPageResults = "&maxPageResults=" . $searchParams['maxPageResults'];
    	}
    	return  "{$url}/abandoned/?".$connectionData->getCredentialsUrlQuery()."&initialDate={$initialDate}&finalDate={$finalDate}{$page}{$maxPageResults}";
    }    
    
    
	
    /**
     * Finds a transaction with a matching transaction code
     * 
     * @param PagSeguroCredentials $credentials
     * @param String $transactionCode
     * @return a transaction object
     * @see PagSeguroTransaction
     * @throws PagSeguroServiceException
     * @throws Exception
     */
    public static function searchByCode(PagSeguro_Credentials $credentials, $transactionCode) {
		
		PagSeguro_Log::info("PagSeguro_TransactionSearchService.SearchByCode($transactionCode) - begin");
		
		$connectionData = new PagSeguro_ConnectionData($credentials, self::serviceName);
		
		try{
			
			$connection = new PagSeguro_HttpConnection();
			$connection->get(
				self::buildSearchUrlByCode($connectionData, $transactionCode),
				$connectionData->getServiceTimeout(),
				$connectionData->getCharset()
			);
			$httpStatus = new PagSeguro_HttpStatus($connection->getStatus());
			
			switch ($httpStatus->getType()) {
				
				case 'OK':
					$transaction = PagSeguro_TransactionParser::readTransaction($connection->getResponse());
					PagSeguro_Log::info("PagSeguro_TransactionSearchService.SearchByCode(transactionCode=$transactionCode) - end ".$transaction->toString());
					break;
					
				case 'BAD_REQUEST':
					$errors = PagSeguro_TransactionParser::readErrors($connection->getResponse());
					$e = new PagSeguro_ServiceException($httpStatus, $errors);
					PagSeguro_Log::error("PagSeguro_TransactionSearchService.SearchByCode(transactionCode=$transactionCode) - error ".$e->getOneLineMessage());
					throw $e;
					break;
					
				default:
					$e = new PagSeguro_ServiceException($httpStatus);
					PagSeguro_Log::error("PagSeguro_TransactionSearchService.SearchByCode(transactionCode=$transactionCode) - error ".$e->getOneLineMessage());
					throw $e;
					break;
					
			}
			
			return isset($transaction) ? $transaction : false;
			
		} catch (PagSeguro_ServiceException $e) {
			throw $e;
		} catch (Exception $e) {
			PagSeguro_Log::error("Exception: ".$e->getMessage());
			throw $e;
		}
		
    }
	
    
    /**
     * Search transactions associated with this set of credentials within a date range
     * 
     * @param PagSeguroCredentials $credentials
     * @param integer $pageNumber
     * @param integer $maxPageResults
     * @param String $initialDate
     * @param String $finalDate
     * @return a object of PagSeguroTransactionSerachResult class
     * @see PagSeguroTransactionSearchResult
     * @throws PagSeguroServiceException
     * @throws Exception
     */
    public static function searchByDate(PagSeguro_Credentials $credentials, $pageNumber, $maxPageResults, $initialDate, $finalDate = null) {
		
		PagSeguro_Log::info("PagSeguro_TransactionSearchService.SearchByDate(initialDate=".PagSeguro_Helper::formatDate($initialDate).", finalDate=".PagSeguro_Helper::formatDate($finalDate).") - begin");
		
		$connectionData = new PagSeguro_ConnectionData($credentials, self::serviceName);
		
		$searchParams = Array(
			'initialDate' 	 => PagSeguro_Helper::formatDate($initialDate),
			'pageNumber' 	 => $pageNumber,
			'maxPageResults' => $maxPageResults
		);
		
		$searchParams['finalDate'] = $finalDate ? PagSeguro_Helper::formatDate($finalDate) : null;
		
		try{
			
			$connection = new PagSeguro_HttpConnection();
			$connection->get(
				self::buildSearchUrlByDate($connectionData, $searchParams),
				$connectionData->getServiceTimeout(),
				$connectionData->getCharset()
			);
			
			$httpStatus = new PagSeguro_HttpStatus($connection->getStatus());
			
			switch ($httpStatus->getType()) {
				
				case 'OK':
					$searchResult = PagSeguro_TransactionParser::readSearchResult($connection->getResponse());
					PagSeguro_Log::info("PagSeguro_TransactionSearchService.SearchByDate(initialDate=".PagSeguro_Helper::formatDate($initialDate).", finalDate=".PagSeguro_Helper::formatDate($finalDate).") - end ".$searchResult->toString());
					break;
					
				case 'BAD_REQUEST':
					$errors = PagSeguro_TransactionParser::readErrors($connection->getResponse());
					$e = new PagSeguro_ServiceException($httpStatus, $errors);
					PagSeguro_Log::error("PagSeguro_TransactionSearchService.SearchByDate(initialDate=".PagSeguro_Helper::formatDate($initialDate).", finalDate=".PagSeguro_Helper::formatDate($finalDate).") - end ".$e->getOneLineMessage());
					throw $e;
					break;
					
				default:
					$e = new PagSeguro_ServiceException($httpStatus);
					PagSeguro_Log::error("PagSeguro_TransactionSearchService.SearchByDate(initialDate=".PagSeguro_Helper::formatDate($initialDate).", finalDate=".PagSeguro_Helper::formatDate($finalDate).") - end ".$e->getOneLineMessage());
					throw $e;
					break;
					
			}
			
			return isset($searchResult) ? $searchResult : false;
			
		} catch (PagSeguro_ServiceException $e) {
			throw $e;
		} catch (Exception $e) {
			PagSeguro_Log::error("Exception: ".$e->getMessage());
			throw $e;
		}
		
    }
    
    
    /**
    * Search transactions abandoned associated with this set of credentials within a date range
    *
    * @param PagSeguroCredentials $credentials
    * @param String $initialDate
    * @param String $finalDate
    * @param integer $pageNumber
    * @param integer $maxPageResults
    * @return a object of PagSeguroTransactionSerachResult class
    * @see PagSeguroTransactionSearchResult
    * @throws PagSeguroServiceException
    * @throws Exception
    */
    public static function searchAbandoned(PagSeguro_Credentials $credentials, $pageNumber, $maxPageResults, $initialDate, $finalDate = null) {
    
    	PagSeguro_Log::info("PagSeguro_TransactionSearchService.searchAbandoned(initialDate=".PagSeguro_Helper::formatDate($initialDate).", finalDate=".PagSeguro_Helper::formatDate($finalDate).") - begin");
    
    	$connectionData = new PagSeguro_ConnectionData($credentials, self::serviceName);
    
    	$searchParams = Array(
    			'initialDate' 	 => PagSeguro_Helper::formatDate($initialDate),
    			'pageNumber' 	 => $pageNumber,
    			'maxPageResults' => $maxPageResults
    	);
		
		$searchParams['finalDate'] = $finalDate ? PagSeguro_Helper::formatDate($finalDate) : null;
    
    	try{
    			
    		$connection = new PagSeguro_HttpConnection();
    		$connection->get(
	    		self::buildSearchUrlAbandoned($connectionData, $searchParams),
	    		$connectionData->getServiceTimeout(),
	    		$connectionData->getCharset()
    		);
    			
    		$httpStatus = new PagSeguro_HttpStatus($connection->getStatus());
    			
    		switch ($httpStatus->getType()) {
    
    			case 'OK':
    				$searchResult = PagSeguro_TransactionParser::readSearchResult($connection->getResponse());
    				PagSeguro_Log::info("PagSeguro_TransactionSearchService.searchAbandoned(initialDate=".PagSeguro_Helper::formatDate($initialDate).", finalDate=".PagSeguro_Helper::formatDate($finalDate).") - end ".$searchResult->toString());
    				break;
    					
    			case 'BAD_REQUEST':
    				$errors = PagSeguro_TransactionParser::readErrors($connection->getResponse());
    				$e = new PagSeguro_ServiceException($httpStatus, $errors);
    				PagSeguro_Log::error("PagSeguro_TransactionSearchService.searchAbandoned(initialDate=".PagSeguro_Helper::formatDate($initialDate).", finalDate=".PagSeguro_Helper::formatDate($finalDate).") - end ".$e->getOneLineMessage());
    				throw $e;
    				break;
    					
    			default:
    				$e = new PagSeguro_ServiceException($httpStatus);
					PagSeguro_Log::error("PagSeguro_TransactionSearchService.searchAbandoned(initialDate=".PagSeguro_Helper::formatDate($initialDate).", finalDate=".PagSeguro_Helper::formatDate($finalDate).") - end ".$e->getOneLineMessage());
					throw $e;
    			break;
    				
    		}
    			
    		return isset($searchResult) ? $searchResult : false;
    			
    	} catch (PagSeguro_ServiceException $e) {
    		throw $e;
    	} catch (Exception $e) {
    		PagSeguro_Log::error("Exception: ".$e->getMessage());
    		throw $e;
    	}
    
    }    
	
}

?>