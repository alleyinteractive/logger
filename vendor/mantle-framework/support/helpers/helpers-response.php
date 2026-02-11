<?php
/**
 * This file contains assorted helpers for responses
 *
 * @phpcs:disable Squiz.Commenting.FunctionComment
 * @phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
 *
 * @package Mantle
 */

declare(strict_types=1);

namespace Mantle\Support\Helpers;

use Mantle\Testing\Exceptions\Exit_Simulation_Exception;

/**
 * Terminate the request, simulating an exit call.
 *
 * If running in a unit testing environment, an
 * `Exit_Simulation_Exception` will be thrown instead of calling `exit()`.
 *
 * @param int                  $exit_status The exit status code. 0 indicates a normal exit.
 * @param int|null             $response_code The HTTP response code, optional. If not provided, will default to 200.
 * @param array<string,string> $headers Optional headers to send before terminating.
 *
 * @throws \RuntimeException If the `Exit_Simulation_Exception` class is not found during unit testing.
 * @throws \Mantle\Testing\Exceptions\Exit_Simulation_Exception Thrown when in a unit testing environment.
 */
function terminate_request( int $exit_status = 0, ?int $response_code = 200, array $headers = [] ): never {
	if ( is_unit_testing() ) {
		if ( ! class_exists( Exit_Simulation_Exception::class ) ) {
			throw new \RuntimeException( Exit_Simulation_Exception::class . ' not found. Please ensure that mantle-framework/testing is installed.' );
		}

		throw new Exit_Simulation_Exception(
			exit_status: $exit_status,
			response_code: $response_code,
			headers: $headers,
		);
	}

	if ( null !== $response_code ) {
		status_header( $response_code );
	}

	if ( ! headers_sent() ) {
		foreach ( $headers as $name => $value ) {
			header( "{$name}: {$value}" );
		}
	}

	exit( $exit_status ); // phpcs:ignore WordPress
}

/**
 * Mirrors wp_send_json() but uses terminate_request() to allow for testing.
 *
 * @param mixed    $data Data to be sent as JSON.
 * @param int|null $status_code HTTP status code to send.
 * @param int      $flags Optional. JSON encoding flags. Default 0.
 */
function send_json_response( mixed $data, ?int $status_code = 200, int $flags = 0 ): never {
	$content_type = 'application/json; charset=' . get_option( 'blog_charset' );
	// Send the header in advance of sending the output if not unit testing.
	if ( ! headers_sent() && ! is_unit_testing() ) {
		header( 'Content-Type: ' . $content_type );
	}

	echo wp_json_encode( $data, $flags ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	terminate_request(
		response_code: $status_code,
		headers: [ 'Content-Type' => $content_type ],
	);
}
