<?php
/**
 * This file is part of the Unsplash App
 * and licensed under the AGPL.
 */

namespace OCA\Unsplash\Services;

use OCP\IConfig;
use Psr\Log\LoggerInterface;
use OCA\Unsplash\ProviderHandler\Provider;
use OCA\Unsplash\ProviderHandler\ProviderDefinitions;

/**
 * Class SettingsService
 *
 * @package OCA\Unsplash\Services
 */
class SettingsService {

    const STYLE_LOGIN          = 'unsplash/style/login';
    const STYLE_LOGIN_HIGH_VISIBILITY = 'unsplash/style/login/highvisibility';
    const STYLE_DASHBORAD      = 'unsplash/style/dashborad';
    const USER_STYLE_DASHBORAD = 'unsplash/style/dashborad';
    const PROVIDER_SELECTED    = 'unsplash/provider/selected';
    const PROVIDER_DEFAULT     = 'Unsplash';

    const STYLE_TINT_ALLOWED = 'unsplash/style/tint';
    const STYLE_STRENGHT_COLOR = 'unsplash/style/strength/color';
    const STYLE_STRENGHT_BLUR = 'unsplash/style/strength/blur';

    const STYLE_TINT_ALLOWED_DEFAULT = 0; //equals 30%
    const STYLE_STRENGHT_COLOR_DEFAULT = 30; //equals 30%
    const STYLE_STRENGHT_BLUR_DEFAULT = 0;

    const STYLE_LOGIN_HIGH_VISIBILITY_DEFAULT = 0;

    /**
     * @var IConfig
     */
    protected $config;

    /**
     * @var string
     */
    protected $userId;

    /**
     * @var string
     */
    protected $appName;

	/**
	 * @var ProviderDefinitions
	 */
	protected $providerDefinitions;

	/**
	 * @var \OC_Defaults
	 */
	private $defaults;

    /**
     * FaviconService constructor.
     *
     * @param string|null $userId
     * @param             $appName
     * @param IConfig     $config
     * @param Defaults     $defaults
     */
    public function __construct($userId, $appName, IConfig $config, \OC_Defaults $defaults) {
        $this->config = $config;
        $this->userId = $userId;
        if($this->config->getSystemValue('maintenance', false)) {
            $this->userId = null;
        }
        $this->appName = $appName;

        $this->providerDefinitions = new ProviderDefinitions($this->appName,$this->config);
		$this->defaults = $defaults;
    }

    /**
     * If the dashboard should be styled for this user
     *
     * @return bool
     */
    public function getUserStyleDashboardEnabled(): bool {
        $themingAppDashboard = $this->config->getUserValue($this->userId, "theming", 'background', 'default');

        // dont add custom css when custom image was selected
        if($themingAppDashboard == 'default') {
            return $this->getServerStyleDashboardEnabled();
        }
        return false;
    }

    /**
     * If the page dashboard be styled by default
     *
     * @return bool
     */
    public function getServerStyleDashboardEnabled(): bool {
        return $this->config->getAppValue($this->appName, self::STYLE_DASHBORAD, 0) == 1;
    }

    /**
     * Set if the dashboard should be styled by default
     *
     * @param int $styleDashboard
     */
    public function setServerStyleDashboardEnabled(int $styleDashboard = 1) {
        $this->config->setAppValue($this->appName, self::STYLE_DASHBORAD, $styleDashboard);
    }

    /**
     * If the login page should be styled by default
     *
     * @return bool
     */
    public function getServerStyleLoginEnabled(): bool {
        return $this->config->getAppValue($this->appName, self::STYLE_LOGIN, 1) == 1;
    }

    /**
     * Set if the login page should be styled by default
     *
     * @param int $styleLogin
     */
    public function setServerStyleLoginEnabled(int $styleLogin = 1) {
        $this->config->setAppValue($this->appName, self::STYLE_LOGIN, $styleLogin);
    }

    /**
     * Todo: refactor this function to a "has dash" function that also checks wether the dashboard is actually enabled.
     *       and then dont show the entries.
     * @return int
     */
    public function getNextcloudVersion(): int {
        $version = $this->config->getSystemValue('version', '0.0.0');
        $parts = explode('.', $version, 2);

        return intval($parts[0]);
    }

	/**
	 * Set the selected imageprovider
	 *
	 * @param string $providername
	 */
	public function setImageProvider(string $providername): void {
		$this->config->setAppValue($this->appName, self::PROVIDER_SELECTED, $providername);
	}

	/**
	 * Get the selected imageprovider's name
	 *
	 * @return string current provider name
	 */
	public function getImageProviderName(): string {
		return $this->config->getAppValue($this->appName, self::PROVIDER_SELECTED, self::PROVIDER_DEFAULT);
	}


    /**
     * Get the selected imageprovider
     *
     * @return string name of the provider
     * @return string current provider
     */
    public function getImageProvider($name): Provider {
        return $this->providerDefinitions->getProviderByName($name);
    }


    /**
     * Get the selected imageprovider customization
     *
     * @return string current provider customization
     */
    public function getImageProviderCustomization() {
        $providername = $this->getImageProviderName();
        $provider = $this->providerDefinitions->getProviderByName($providername);
        return $provider->getCustomSearchterms();
    }

    /**
     * Set the selected imageprovider customization
     *
     * @param string $customization
     */
    public function setImageProviderCustomization($customization) {
        $providername = $this->getImageProviderName();
        $provider = $this->providerDefinitions->getProviderByName($providername);
        $provider->setCustomSearchTerms($customization);
    }

	/**
	 * Get all defined imageprovider
	 */
	public function getAllImageProvider() {
		return $this->providerDefinitions->getAllProviderNames();
	}

    /**
     * Get all defined imageprovider that allow customization
     */
    public function getAllCustomizableImageProvider()
    {
        $all = [];
        foreach ($this->providerDefinitions->getAllProviderNames() as $value) {
            $provider = $this->providerDefinitions->getProviderByName($value);
            if($provider->isCustomizable()){
                $all[] = $value;
            }
        }
        return $this->providerDefinitions->getAllProviderNames();
    }


	/**
	 * Returns the URL to the custom Unsplash-path
	 *
	 * @return String
	 */
	public function headerbackgroundLink($size) {
		$providerName = $this->config->getAppValue($this->appName, self::PROVIDER_SELECTED, self::PROVIDER_DEFAULT);
		$provider = $this->providerDefinitions->getProviderByName($providerName);
		return $provider->getRandomImageUrl($size);
	}

	/**
	 * Get all URLs for whitelisting
	 */
	public function getWhitelistingUrlsForSelectedProvider() {
		$providerName = $this->config->getAppValue($this->appName, self::PROVIDER_SELECTED, self::PROVIDER_DEFAULT);
		$provider = $this->providerDefinitions->getProviderByName($providerName);
		return $provider->getWhitelistResourceUrls();
	}
	/**
	 * nextcloud theming main color
	 *
	 * @param String $styleUrl
	 */
	public function getInstanceColor() {
		return $this->config->getAppValue('theming', 'color', $this->defaults->getColorPrimary());
	}

	/**
	 * If the login page should be styled by default
	 *
	 * @return bool
	 */
	public function isTintEnabled(): bool {
		return $this->config->getAppValue($this->appName, self::STYLE_TINT_ALLOWED, self::STYLE_TINT_ALLOWED_DEFAULT);
	}

	public function setTint(int $tinting): void {
		$this->config->setAppValue($this->appName, self::STYLE_TINT_ALLOWED, $tinting);
	}


	/**
	 * color strength
	 *
	 * @param String $styleUrl
	 */
	public function getColorStrength() {
		return $this->config->getAppValue($this->appName, self::STYLE_STRENGHT_COLOR, self::STYLE_STRENGHT_COLOR_DEFAULT);
	}

	/**
	 * set color strength
	 *
	 * @param String $styleUrl
	 */
	public function setColorStrength(int $strength) {
		if($strength>100){
			$strength=100;
		}
		if($strength<0){
			$strength=0;
		}
		$this->config->setAppValue($this->appName, self::STYLE_STRENGHT_COLOR, $strength);
	}


	/**
	 * blur strength
	 *
	 * @param String $styleUrl
	 */
	public function getBlurStrength() {
		return $this->config->getAppValue($this->appName, self::STYLE_STRENGHT_BLUR, self::STYLE_STRENGHT_BLUR_DEFAULT);
	}
	/**
	 * set blur strength
	 *
	 * @param String $styleUrl
	 */
	public function setBlurStrength(int $strength) {
		if($strength>25){
			$strength=25;
		}
		if($strength<0){
			$strength=0;
		}
		$this->config->setAppValue($this->appName, self::STYLE_STRENGHT_BLUR, $strength);
	}

    /**
     * If the login page should be styled as High Visibility for Legal reasons
     *
     * @return bool
     */
    public function isHighVisibilityLogin(): bool {
        return $this->config->getAppValue($this->appName, self::STYLE_LOGIN_HIGH_VISIBILITY, self::STYLE_LOGIN_HIGH_VISIBILITY_DEFAULT);
    }

    public function setHighVisibilityLogin(int $highVisibility): void {
        $this->config->setAppValue($this->appName, self::STYLE_LOGIN_HIGH_VISIBILITY, $highVisibility);
    }

}
