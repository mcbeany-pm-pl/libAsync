<?php

declare(strict_types=1);

namespace Mcbeany\libAsync;

use Closure;
use Generator;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;
use Throwable;

final class libAsync {
	/**
	 * Call a function asynchronously.
	 *
	 * @template T
	 * @param Closure(): T $executor
	 *
	 * @return Generator<mixed, mixed, mixed, T>
	 */
	public static function doAsync(Closure $executor): Generator {
		$resolve = yield Await::RESOLVE;
		$reject = yield Await::REJECT;
		$task = new class($executor, $resolve, $reject) extends AsyncTask {
			private bool $rejected = false;
			public function __construct(
				private Closure $executor,
				Closure $resolve,
				Closure $reject
			) {
				$this->storeLocal('resolve', $resolve);
				$this->storeLocal('reject', $reject);
			}

			public function onRun(): void {
				try {
					$result = ($this->executor)();
				} catch (Throwable $e) {
					$this->rejected = true;
					$result = $e;
				}
				$this->setResult($result);
			}

			public function onCompletion(): void {
				/** @var T|Throwable $result */
				$result = $this->getResult();
				if ($this->rejected && $result instanceof Throwable) {
					/** @var Closure(Throwable): void */
					$reject = $this->fetchLocal('reject');
					$reject($result);
					return;
				}
				/** @var Closure(T): void $resolve */
				$resolve = $this->fetchLocal('resolve');
				$resolve($result);
			}
		};
		Server::getInstance()->getAsyncPool()->submitTask($task);
		return yield Await::ONCE;
	}
}
