<?php

declare( strict_types=1 );

final class KnownAgentsRobotsBuilder {
	private const API_URL = 'https://api.knownagents.com/robots-txts';

	/**
	 * Policy presets mapped to Known Agents agent_types.
	 *
	 * @var array<string, array<int, string>>
	 */
	private const PRESETS = [
		'training-only' => [
			'AI Data Scraper',
		],
		'training-plus-undocumented' => [
			'AI Data Scraper',
			'Undocumented AI Agent',
		],
		'aggressive' => [
			'AI Data Scraper',
			'Undocumented AI Agent',
			'Scraper',
			'Intelligence Gatherer',
		],
	];

	/**
	 * Fetch robots.txt text from Known Agents.
	 *
	 * @param string $token Bearer token for the project.
	 * @param array<int, string> $agent_types Known Agents categories to request.
	 * @param string $disallow Path to disallow. Defaults to '/'.
	 * @return string
	 */
	public static function fetch_robots_txt( string $token, array $agent_types, string $disallow = '/' ): string {
		if ( '' === trim( $token ) ) {
			throw new InvalidArgumentException( 'Known Agents token is required.' );
		}

		if ( empty( $agent_types ) ) {
			throw new InvalidArgumentException( 'At least one agent type is required.' );
		}

		$payload = json_encode(
			[
				'agent_types' => array_values( $agent_types ),
				'disallow'    => $disallow,
			],
			JSON_UNESCAPED_SLASHES
		);

		if ( false === $payload ) {
			throw new RuntimeException( 'Failed to encode Known Agents request payload.' );
		}

		$ch = curl_init( self::API_URL );

		if ( false === $ch ) {
			throw new RuntimeException( 'Failed to initialize cURL.' );
		}

		curl_setopt_array(
			$ch,
			[
				CURLOPT_POST           => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTPHEADER     => [
					'Authorization: Bearer ' . $token,
					'Content-Type: application/json',
				],
				CURLOPT_POSTFIELDS     => $payload,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_CONNECTTIMEOUT => 10,
			]
		);

		$response = curl_exec( $ch );
		$errno    = curl_errno( $ch );
		$error    = curl_error( $ch );
		$code     = (int) curl_getinfo( $ch, CURLINFO_RESPONSE_CODE );

		curl_close( $ch );

		if ( 0 !== $errno ) {
			throw new RuntimeException( 'Known Agents request failed: ' . $error );
		}

		if ( ! is_string( $response ) ) {
			throw new RuntimeException( 'Known Agents response was not a string.' );
		}

		if ( 200 > $code || 300 <= $code ) {
			throw new RuntimeException(
				sprintf(
					'Known Agents API returned HTTP %d. Response: %s',
					$code,
					$response
				)
			);
		}

		return $response;
	}

	/**
	 * Extract user-agent tokens from robots.txt text.
	 *
	 * @param string $robots_txt
	 * @return array<int, string>
	 */
	public static function extract_user_agents( string $robots_txt ): array {
		$lines = preg_split( '/\R/', $robots_txt );

		if ( false === $lines ) {
			return [];
		}

		$seen = [];
		$out  = [];

		foreach ( $lines as $line ) {
			$line = trim( $line );

			if ( '' === $line || str_starts_with( $line, '#' ) ) {
				continue;
			}

			if ( ! preg_match( '/^User-agent:\s*(.+)$/i', $line, $matches ) ) {
				continue;
			}

			$token = trim( $matches[1] );

			if ( '' === $token || '*' === $token ) {
				continue;
			}

			$key = strtolower( $token );

			if ( isset( $seen[ $key ] ) ) {
				continue;
			}

			$seen[ $key ] = true;
			$out[]        = $token;
		}

		natcasesort( $out );

		return array_values( $out );
	}

	/**
	 * Build a PHP file that returns metadata + user agent list.
	 *
	 * @param array<int, string> $user_agents
	 * @param array<int, string> $agent_types
	 * @return string
	 */
	public static function build_php_array_file( array $user_agents, array $agent_types ): string {
		$export = var_export(
			[
				'generated_at' => gmdate( 'c' ),
				'agent_types'  => array_values( $agent_types ),
				'user_agents'  => array_values( $user_agents ),
			],
			true
		);

		return <<<PHP
<?php
declare(strict_types=1);

/**
 * Generated file. Do not edit by hand.
 */

return {$export};

PHP;
	}

	/**
	 * Convenience helper for presets.
	 *
	 * @param string $preset
	 * @return array<int, string>
	 */
	public static function get_agent_types_for_preset( string $preset ): array {
		if ( ! isset( self::PRESETS[ $preset ] ) ) {
			throw new InvalidArgumentException( 'Unknown preset: ' . $preset );
		}

		return self::PRESETS[ $preset ];
	}
}
