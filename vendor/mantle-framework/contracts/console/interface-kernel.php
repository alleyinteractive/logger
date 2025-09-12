<?php
/**
 * Kernel interface file.
 *
 * @package Mantle
 */

namespace Mantle\Contracts\Console;

use Closure;
use Mantle\Console\Closure_Command;
use Mantle\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Console Kernel
 */
interface Kernel extends \Mantle\Contracts\Kernel {
	/**
	 * Run the console application.
	 *
	 * @param InputInterface       $input Console input.
	 * @param OutputInterface|null $output Console output.
	 */
	public function handle( InputInterface $input, ?OutputInterface $output = null ): int;

	/**
	 * Run the console application by command name.
	 *
	 * @param string               $command Command name.
	 * @param array<mixed>         $parameters Command parameters.
	 * @param OutputInterface|null $output_buffer Output buffer.
	 */
	public function call( string $command, array $parameters = [], ?OutputInterface $output_buffer = null ): int;

	/**
	 * Run the console application by command name without output.
	 *
	 * @param string       $command Command name.
	 * @param array<mixed> $parameters Command parameters.
	 */
	public function call_silently( string $command, array $parameters = [] ): int;

	/**
	 * Test a console command by name.
	 *
	 * @param string       $command Command name.
	 * @param array<mixed> $parameters Command parameters.
	 */
	public function test( string $command, array $parameters = [] ): CommandTester;

	/**
	 * Register the application's commands.
	 */
	public function register_commands(): void;

	/**
	 * Register a new command with the console application.
	 *
	 * @param Command|class-string<Command> $command Command instance or class name.
	 */
	public function register( Command|string $command ): void;

	/**
	 * Register a new Closure based command with a signature.
	 *
	 * @param string  $signature Command signature.
	 * @param Closure $callback Command callback.
	 */
	public function command( string $signature, Closure $callback ): Closure_Command;

	/**
	 * Log to the console.
	 *
	 * @param string $message Message to log.
	 */
	public function log( string $message ): void;

	/**
	 * Terminate the application.
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface $input
	 * @param  int                                             $status
	 */
	public function terminate( $input, $status ): void;
}
