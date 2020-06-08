<?php
/**
 * This file is part of the Unsplash App
 * and licensed under the AGPL.
 */

namespace OCA\Unsplash\AppInfo;

use OC\Security\CSP\ContentSecurityPolicy;
use OCA\Unsplash\Services\SettingsService;
use OCP\AppFramework\App;
use OCP\Util;

/**
 * Class Application
 *
 * @package OCA\Unsplash\AppInfo
 */
class Application extends App {

    /**
     * Application constructor.
     *
     * @param array $urlParams
     */
    public function __construct(array $urlParams = []) {
        parent::__construct('unsplash', $urlParams);
    }

    /**
     * Register all app functionality
     *
     * @throws \OCP\AppFramework\QueryException
     */
    public function register() {
        $this->registerPersonalSettings();
        $this->registerStyleSheets();
        $this->registerCsp();
    }

    /**
     * Add the personal settings page
     */
    public function registerPersonalSettings() {
        \OCP\App::registerPersonal('unsplash', 'templates/personal');
    }

    /**
     * Add the stylesheets
     *
     * @throws \OCP\AppFramework\QueryException
     */
    public function registerStyleSheets() {
        /** @var SettingsService $settings */
        $settings = $this->getContainer()->query(SettingsService::class);

		$unsplashScript = \OC::$WEBROOT;

        if($settings->getUserStyleHeaderEnabled()) {
            //Util::addStyle('unsplash', 'header');
			Util::addHeader(
				'link',
				[
					'rel'  => "stylesheet",
					'type' =>"text/css",
					'href' => $unsplashScript."/apps/unsplash/lib/CssBuilder/header.php",
				]
			);
        }

        if($settings->getServerStyleLoginEnabled()) {
            //Util::addStyle('unsplash', 'login');
			Util::addHeader(
				'link',
				[
					'rel'  => "stylesheet",
					'type' =>"text/css",
					'href' => $unsplashScript."/apps/unsplash/lib/CssBuilder/login.php",
				]
			);
        }
    }

    /**
     * Allow Unsplash hosts in the csp
     *
     * @throws \OCP\AppFramework\QueryException
     */
    public function registerCsp() {
        /** @var SettingsService $settings */
        $settings = $this->getContainer()->query(SettingsService::class);

        if($settings->getUserStyleHeaderEnabled() || $settings->getServerStyleLoginEnabled()) {
            $manager = $this->getContainer()->getServer()->getContentSecurityPolicyManager();
            $policy  = new ContentSecurityPolicy();

			$urls = $settings->getWhitelistingUrls();
			foreach ($urls as &$value) {
				$policy->addAllowedImageDomain($value);
			}
            $manager->addDefaultPolicy($policy);
        }
    }

}