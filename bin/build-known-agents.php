<?php
declare(strict_types=1);

require_once __DIR__ . '/classes/KnownAgentsRobotsBuilder.php';

$options = getopt(
	'',
	[
		'token:',
		'preset::',
		'types::',
		'robots-out::',
		'php-out:',
		'disallow::',
	]
);

$token = $options['token'] ?? getenv( 'KNOWN_AGENTS_TOKEN' ) ?: '';

if ( '' === trim( $token ) ) {
	fwrite( STDERR, "Missing token. Pass --token=... or set KNOWN_AGENTS_TOKEN.\n" );
	exit( 1 );
}

$disallow = $options['disallow'] ?? '/';

if ( ! empty( $options['types'] ) ) {
	$agent_types = array_values(
		array_filter(
			array_map(
				'trim',
				explode( ',', (string) $options['types'] )
			)
		)
	);
} else {
	$preset      = $options['preset'] ?? 'training-only';
	$agent_types = KnownAgentsRobotsBuilder::get_agent_types_for_preset( (string) $preset );
}

$robots_out = $options['robots-out'] ?? null;
$php_out    = $options['php-out'] ?? null;

if ( empty( $php_out ) ) {
	fwrite( STDERR, "Missing required --php-out=/path/to/file.php\n" );
	exit( 1 );
}

try {
	$robots_txt  = KnownAgentsRobotsBuilder::fetch_robots_txt( $token, $agent_types, (string) $disallow );
	$user_agents = KnownAgentsRobotsBuilder::extract_user_agents( $robots_txt );
	$php_file    = KnownAgentsRobotsBuilder::build_php_array_file( $user_agents, $agent_types );

	if ( is_string( $robots_out ) && '' !== $robots_out ) {
		$dir = dirname( $robots_out );

		if ( ! is_dir( $dir ) && ! mkdir( $dir, 0777, true ) && ! is_dir( $dir ) ) {
			throw new RuntimeException( 'Failed to create robots output directory: ' . $dir );
		}

		file_put_contents( $robots_out, $robots_txt );
	}

	$php_dir = dirname( $php_out );

	if ( ! is_dir( $php_dir ) && ! mkdir( $php_dir, 0777, true ) && ! is_dir( $php_dir ) ) {
		throw new RuntimeException( 'Failed to create PHP output directory: ' . $php_dir );
	}

	file_put_contents( $php_out, $php_file );

	fwrite(
		STDOUT,
		sprintf(
			"Done. Extracted %d user-agent tokens.\n",
			count( $user_agents )
		)
	);
} catch ( Throwable $e ) {
	fwrite( STDERR, $e->getMessage() . "\n" );
	exit( 1 );
}
