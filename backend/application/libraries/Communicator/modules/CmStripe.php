<?php
require_once CM_LIBS_PATH . 'external/stripe-php-master/init.php';

class CmStripe extends CmModule
{
	
	public function request ($type, array $params = array()) {
		$response = new stdClass;
		$errObject = null;
		try {
			
			try {
			
				Stripe\Stripe::setApiKey($this->config['stripe_secret_key']);
			
				$result = call_user_func_array(array($this, $type), $params);
			
			} catch(\Stripe\Error\Card $e) {
				// Since it's a decline, \Stripe\Error\Card will be caught
				$body = $e->getJsonBody();
				$err  = $body['error'];
			
				$msg = 'Status is: ' . $e->getHttpStatus() . "\n";
				$msg .= 'Type is: ' . (isset($err['type']) ? $err['type'] : 'null') . "\n";
				$msg .= 'Code is: ' . (isset($err['code']) ? $err['code'] : 'null') . "\n";
				$msg .= 'Param is: ' . (isset($err['param']) ? $err['param'] : 'null') . "\n";
				$msg .= 'Message is: ' . (isset($err['message']) ? $err['message'] : 'null') . "\n";
			
				$errObject = $e->getJsonBody();
				$message = $msg."\n";
				$message = get_class($e).' error: ' . $e->getMessage();
				throw new Exception($message);
			} catch (\Stripe\Error\InvalidRequest $e) {
				// Invalid parameters were supplied to Stripe's API
				//logmes(get_class($e).' error:',$e->getJsonBody(),$stripe_error_log);
				$errObject = $e->getJsonBody();
				$message = get_class($e).' error: ' . var_export($e->getJsonBody(), true);
				throw new Exception($message);
			} catch (\Stripe\Error\Authentication $e) {
				// Authentication with Stripe's API failed
				// (maybe you changed API keys recently)
				$errObject = $e->getJsonBody();
				$message = get_class($e).' error: ' . var_export($e->getJsonBody(), true);
				throw new Exception($message);
			} catch (\Stripe\Error\ApiConnection $e) {
				// Network communication with Stripe failed
				$errObject = $e->getJsonBody();
				$message = get_class($e).' error: ' . var_export($e->getJsonBody(), true);
				throw new Exception($message);
			} catch (\Stripe\Error\Base $e) {
				// Display a very generic error to the user
				$errObject = $e->getJsonBody();
				$message = get_class($e).' error: ' . var_export($e->getJsonBody(), true);
				throw new Exception($message);
			} catch (Exception $e) {
				// Something else happened, completely unrelated to Stripe
				$message = get_class($e).' error: ' . $e->getMessage();
				throw new Exception($message);
			}
			
			$response->result = $result;
			$response->error = null;
			$response->errObject = $errObject;
		}
		catch (Exception $e) {
			$this->logger->log("Communicator $type request error in module ". get_class($this) .". ". $e->getMessage().". Params: ", $params);
			$response->result = null;
			$response->error = $e->getMessage();
			$response->errObject = $errObject;
		}
		return $response;
	}
	
	public function charge_all($params = array()) {
		$this->logger->log(__METHOD__.". Params: ", $params);
		return Stripe\Charge::all($params);
	}
	
	public function charge_create($params = array()) {
		$this->logger->log(__METHOD__.". Params: ", $params);
		return Stripe\Charge::create($params);
	}
	
	public function charge_retrieve($params = array()) {
		$this->logger->log(__METHOD__.". Params: ", $params);
		return Stripe\Charge::retrieve($params);
	}
	
}
