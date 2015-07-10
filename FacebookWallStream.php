<?php

namespace Facebook\Wall\Stream;

require __DIR__ . '/facebook-php-sdk/autoload.php';

use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
use Facebook\FacebookResponse;
use Facebook\FacebookSession;

/**
 * Class to interact with posts of a page in a paginated fashion
 * @author Kevin Mauel <kevin.mauel2@gmail.com> <http://www.github.com/bavragor>
 */
class FacebookWallStream
{
	/**
	 * @var string
	 */
	private $apiKey;

	/**
	 * @var string
	 */
	private $appSecret;

	/**
	 * @var FacebookSession
	 */
	private $facebookSession;

	/**
	 * @var string
	 */
	private $appAccessToken;

	/**
	 * @var FacebookResponse
	 */
	private $facebookResponse;

	/**
	 * @var array
	 * @link Possible fields: https://developers.facebook.com/docs/graph-api/reference/v2.3/page/feed#pubfields
	 */
	private $responseFields = ['message', 'picture', 'link', 'icon', 'type', 'created_time', 'updated_time'];

	/**
	 * @var string
	 */
	private $previousRequest;

	/**
	 * @var string
	 */
	private $nextRequest;

	/**
	 * When creating an instance it will create also a FacebookSession for further usage
	 * @param integer $apiKey
	 * @param string $appSecret
	 * @param string $appAccessToken
	 */
	public function __construct(
		$apiKey,
		$appSecret,
		$appAccessToken
	) {
		$this->apiKey = $apiKey;
		$this->appSecret = $appSecret;
		$this->appAccessToken = $appAccessToken;
		FacebookSession::setDefaultApplication($this->apiKey, $this->appSecret);
		$this->facebookSession = new FacebookSession($this->appAccessToken);
	}

	/**
	 * Returns the wall content regarding to the given wallId
	 * @param string $wallId Page to request posts for
	 * @param integer $limit Limit for current and future requests
	 * @return array
	 */
	public function getWallStream(
		$wallId,
		$limit = 50
	) {
		$result = [];

		try {
			$request = new FacebookRequest(
				$this->facebookSession,
				'GET',
				"/$wallId/posts",
				[
					'limit' => $limit,
					'fields' => implode(',', $this->responseFields)
				]
			);
			$response = $request->execute();
			$this->facebookResponse = $response;
			$response = $response->getResponse();

			$this->previousRequest = $response->paging->previous;
			$this->nextRequest = $response->paging->next;

			$result = $this->transformFacebookResponse($response->data);
		} catch (FacebookRequestException $ex) {
			echo $ex->getMessage();
		} catch (\Exception $ex) {
			echo $ex->getMessage();
		}

		return $result;
	}

	/**
	 * Based on previous wall request it will execute the next wall request
	 * @return array
	 */
	public function next()
	{
		$result = [];

		try {
			$request = $this->facebookResponse->getRequestForNextPage();
			$response = $request->execute();
			$this->facebookResponse = $response;
			$response = $response->getResponse();
			$result = $this->transformFacebookResponse($response->data);
		} catch (FacebookRequestException $ex) {
			echo $ex->getMessage();
		} catch (\Exception $ex) {
			echo $ex->getMessage();
		}

		return $result;
	}

	/**
	 * Based on previous wall request it will execute the pre previous wall request
	 * @return array
	 */
	public function previous()
	{
		$result = [];

		try {
			$request = $this->facebookResponse->getRequestForPreviousPage();
			$response = $request->execute();
			$this->facebookResponse = $response;
			$response = $response->getResponse();
			$result = $this->transformFacebookResponse($response->data);
		} catch (FacebookRequestException $ex) {
			echo $ex->getMessage();
		} catch (\Exception $ex) {
			echo $ex->getMessage();
		}

		return $result;
	}

	/**
	 * Transforms the facebook response from array of objects to an array of arrays
	 * @param array $response
	 * @return array
	 */
	private function transformFacebookResponse(array $response)
	{
		$return = [];

		/**
		 * @var \stdClass $result
		 */
		foreach ($response as $result) {
			$return[$result->id] = (array) $result;
		}

		return $return;
	}

	/**
	 * @return array
	 */
	public function getResponseFields()
	{
		return $this->responseFields;
	}

	/**
	 * @param string[] $responseFields
	 */
	public function setResponseFields(array $responseFields)
	{
		$this->responseFields = $responseFields;
	}
}