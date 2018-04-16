<?php
/**
 * @copyright Copyright (c) 2018 Alexey Abel <dev@abelonline.de>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\UserBackendSqlRaw;

use \OCP\IConfig;
use OCP\ILogger;

class Config {
	private $logger;
	private $appConfiguration;

	const DEFAULT_DB_HOST = 'localhost';
	const DEFAULT_DB_PORT = '5432';

	const CONFIG_KEY = 'user_backend_sql_raw';
	const CONFIG_KEY_DB_HOST = 'dbHost';
	const CONFIG_KEY_DB_PORT = 'dbPort';
	const CONFIG_KEY_DB_NAME = 'dbName';
	const CONFIG_KEY_DB_USER = 'dbUser';
	const CONFIG_KEY_DB_PASSWORD = 'dbPassword';

	const CONFIG_KEY_QUERIES = 'queries';
	const CONFIG_KEY_GET_PASSWORD_HASH_FOR_USER = 'getPasswordHashForUser';
	const CONFIG_KEY_USER_EXISTS = 'userExists';

	public function __construct(ILogger $logger, IConfig $nextCloudConfiguration) {
		$this->logger = $logger;
		$this->appConfiguration = $nextCloudConfiguration->getSystemValue(self::CONFIG_KEY);
		$this->checkAppConfigurationAndLogErrors();
	}

	/**
	 * @return string db host to connect to
	 */
	public function getDbHost() {
		return $this->appConfiguration[self::CONFIG_KEY_DB_HOST];
	}

	/**
	 * @return int db port to connect to
	 */
	public function getDbPort() {
		return $this->appConfiguration[self::CONFIG_KEY_DB_PORT];
	}

	/**
	 * @return string db name to connect to
	 */
	public function getDbName() {
		return $this->appConfiguration[self::CONFIG_KEY_DB_NAME];
	}

	/**
	 * @return string db user to connect as
	 */
	public function getDbUser() {
		return $this->appConfiguration[self::CONFIG_KEY_DB_USER];
	}

	/**
	 * @return string password of db user
	 * @see getDbUser
	 */
	public function getDbPassword() {
		return $this->appConfiguration[self::CONFIG_KEY_DB_PASSWORD];
	}

	/**
	 * @return string SQL query for retrieving a password hash of a user
	 */
	public function getQueryGetPasswordHashForUser() {
		return $this->appConfiguration[self::CONFIG_KEY_QUERIES][self::CONFIG_KEY_GET_PASSWORD_HASH_FOR_USER];
	}

	/**
	 * @return string SQL query that checks if a user exists
	 */
	public function getQueryUserExists() {
		return $this->appConfiguration[self::CONFIG_KEY_QUERIES][self::CONFIG_KEY_USER_EXISTS];
	}

	/**
	 * Checks the configuration that was read from config.php and logs errors if
	 * configuration keys are missing. Because port and host have default values
	 * their absence will be only logged with info severity.
	 */
	private function checkAppConfigurationAndLogErrors() {
		$logContext = ['app' => 'user_backend_sql_raw'];
		// mandatory keys
		if (empty($this->appConfiguration)) {
			$this->logger->critical('The Nextcloud configuration (config/config.php) does not contain the key '
				. self::CONFIG_KEY . ' which is should contain this apps configuration.');
		} else {
			if (empty($this->appConfiguration[self::CONFIG_KEY_DB_NAME])) {
				$this->logger->critical($this->errorMessageForMandatorySubkey(self::CONFIG_KEY_DB_NAME), $logContext);
			}
			if (empty($this->appConfiguration[self::CONFIG_KEY_DB_USER])) {
				$this->logger->critical($this->errorMessageForMandatorySubkey(self::CONFIG_KEY_DB_USER), $logContext);
			}
			if (empty($this->appConfiguration[self::CONFIG_KEY_DB_PASSWORD])) {
				$this->logger->critical($this->errorMessageForMandatorySubkey(self::CONFIG_KEY_DB_PASSWORD), $logContext);
			}
			// optional keys
			if (empty($this->appConfiguration[self::CONFIG_KEY_DB_HOST])) {
				$this->logger->info('The config key ' . self::CONFIG_KEY_DB_HOST
					. ' is not set, defaulting to host ' . self::DEFAULT_DB_HOST . '.', $logContext);
			}
			if (empty($this->appConfiguration[self::CONFIG_KEY_DB_PORT])) {
				$this->logger->info('The config key ' . self::CONFIG_KEY_DB_PORT
					. ' is not set, defaulting to port ' . self::DEFAULT_DB_PORT . '.', $logContext);
			}
		}
	}

	/**
	 * Returns a full error message and hint for mandatory subkeys.
	 * @param $subkeyName string the name of the subkey
	 * @return string the full error message and hint
	 */
	private function errorMessageForMandatorySubkey($subkeyName) {
		return 'The config key ' . $subkeyName . ' is not set. Add it to config/config.php as a subkey of '
			. self::CONFIG_KEY . '.';
	}
}