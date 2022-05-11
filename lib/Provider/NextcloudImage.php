<?php

/**
 * @copyright Copyright (c) 2019 Felix Nüsse <felix.nuesse@t-online.de>
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

namespace OCA\Unsplash\Provider;
use OCA\Unsplash\Provider\Provider;

class NextcloudImage extends Provider{

	private $THEMING_URL="/index.php/apps/theming/image/background";
	const ALLOW_URL_CUSTOMIZING = false;

	public function getWhitelistResourceUrls(): array
    {
		return [];
	}

	public function getRandomImageUrl(): string
    {
		return $this->THEMING_URL;
	}

	public function getRandomImageUrlBySearchTerm($search): string
    {
		return $this->THEMING_URL;
	}
}
