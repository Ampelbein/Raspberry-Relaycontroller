<?php

class RelayController {

	private static $relays = array(
		'dinnerTable' => 88,
		'livingRoom' => 11,
		'openDoor' => 46,
		'balcony' => 61,
		'strahler' => 89,
		'strahlerBoden' => 65,
        'klima' => 50,
		'klimaAnd' => 115,
		'esstisch' => 60,
		'BueroHoch' => 10,
		'BueroRunter' => 87,
		'BueroStop' => 10,
		'WohnfHoch' => 8,
		'WohnfRunter' => 9,
		'WohnfStop' => 8,
		'WohntHoch' => 76,
		'WohntRunter' => 78,
		'WohntStop' => 76,
		'SusanHoch' => 72,
		'SusanRunter' => 74,
		'SusanStop' => 72,
		'SchlafTuer' => 30,
		'SchlafFenster' => 31,
		'SchlfHoch' => 49,
		'SchlfRunter' => 48,
		'SchlfStop' => 49,
		'GaragHoch' => 26,
		'GaragRunter' => 44,
		'GaragStop' => 26
	);

	private static $rollogpio = array(8, 9, 10, 26, 44, 72, 74, 76, 78, 87, 48, 49);

    private static $rollohochgpio = array(8, 10, 72, 49);

    private static $rolloruntergpio = array(9, 74, 87, 48);

	private static $allTriggered = array(
		'dinnerTable',
		'livingRoom',
		'balcony',
		'strahler',
		'esstisch'
	);

	private static $doors = array(
		'openDoor'
	);

	private static $rollos = array(
		'BueroHoch',
		'BueroRunter',
		'WohnfHoch',
		'WohnfRunter',
		'WohntHoch',
		'WohntRunter',
		'SusanHoch',
		'SusanRunter',
		'SchlfHoch',
		'SchlfRunter',
		'GaragHoch',
		'GaragRunter'
	);

	private static $rollostop = array(
		'BueroStop',
		'WohnfStop',
		'WohntStop',
		'SusanStop',
		'SchlfStop',
		'GaragStop'
	);

	private $gpioNumber;

	public function triggerRelay() {
		$buttonTriggered = $_GET['relay'];
		$relay = substr($buttonTriggered, 0, strlen($buttonTriggered)-1);
		$mode = substr($buttonTriggered, -1) ? 'On' : 'Off';
		$raum = substr($buttonTriggered, 0, 5);
		if ($relay === 'allRollo') {
			$this->rolloStop();
		}  elseif ($relay === 'allRolHo') {
			$this->rolloHoch();
		}  elseif ($relay === 'allRolRu') {
            $this->rolloRunter();
        }  elseif ($relay === 'all') {
			$this->triggerAll($mode);
		}  else {
			$this->gpioNumber = self::$relays[$relay];
			if (in_array($relay, self::$doors)) {
				$this->openDoor();
			} elseif (in_array($relay, self::$rollos)) {
				$function = 'rollo' . $raum;
				$this->{$function}();
				$function = 'relay' . $mode;
				$this->{$function}();
				exec('echo high >> /tmp/gpio' . self::$relays[$relay]);
			} elseif (in_array($relay, self::$rollostop)) {
                $function = 'rollo' . $raum;
                $this->{$function}();
			} else {
				$function = 'relay' . $mode;
				$this->{$function}();
			}
		}
	}

	private function triggerAll($mode) {
		foreach(self::$allTriggered as $relay) {
			$this->gpioNumber = self::$relays[$relay];
			$this->prepareRelay();
		}
		unset($this->gpioNumber);
		unset($relay);
		foreach(self::$allTriggered as $relay) {
			$this->gpioNumber = self::$relays[$relay];
			exec('sleep 1');
			$function = 'relay' . $mode;
			$this->{$function}();
		}
	}

	private function rolloStop() {
		foreach(self::$rollogpio as $rolloNumber) {
		exec('echo high | sudo tee -a /sys/class/gpio/gpio' . $rolloNumber . '/direction');
		}
	}

	private function rolloHoch() {
		foreach(self::$rollogpio as $rolloNumber) {
                exec('echo high | sudo tee -a /sys/class/gpio/gpio' . $rolloNumber . '/direction');
                exec('echo high >> /tmp/gpio' . $rolloNumber . '');
		}
                foreach(self::$rollohochgpio as $rolloNumber) {
                exec('echo low | sudo tee -a /sys/class/gpio/gpio' . $rolloNumber . '/direction');
                exec('echo high >> /tmp/gpio' . $rolloNumber . '');
		}
	}

    private function rolloRunter() {
		foreach(self::$rollogpio as $rolloNumber) {
            exec('echo high | sudo tee -a /sys/class/gpio/gpio' . $rolloNumber . '/direction');
			exec('echo high >> /tmp/gpio' . $rolloNumber . '');
			}
		foreach(self::$rolloruntergpio as $rolloNumber) {
			exec('echo low | sudo tee -a /sys/class/gpio/gpio' . $rolloNumber . '/direction');
			exec('echo high >> /tmp/gpio' . $rolloNumber . '');
			}
	}

	private function rolloBuero() {
		exec('echo high | sudo tee -a /sys/class/gpio/gpio10/direction');
		exec('echo high | sudo tee -a /sys/class/gpio/gpio87/direction');
		exec('sleep 1');
	}

	private function rolloWohnf() {
		exec('echo high | sudo tee -a /sys/class/gpio/gpio8/direction');
		exec('echo high | sudo tee -a /sys/class/gpio/gpio9/direction');
		exec('sleep 1');
	}

	private function rolloWohnt() {
		exec('echo high | sudo tee -a /sys/class/gpio/gpio78/direction');
		exec('echo high | sudo tee -a /sys/class/gpio/gpio76/direction');
		exec('sleep 1');
	}

	private function rolloSusan() {
		exec('echo high | sudo tee -a /sys/class/gpio/gpio74/direction');
		exec('echo high | sudo tee -a /sys/class/gpio/gpio72/direction');
		exec('sleep 1');
	}

	private function rolloSchlt() {
		exec('echo high | sudo tee -a /sys/class/gpio/gpio30/direction');
		exec('echo high | sudo tee -a /sys/class/gpio/gpio31/direction');
		exec('sleep 1');
	}

	private function rolloSchlf() {
		exec('echo high | sudo tee -a /sys/class/gpio/gpio49/direction');
		exec('echo high | sudo tee -a /sys/class/gpio/gpio48/direction');
		exec('sleep 1');
	}

	private function rolloGarag() {
		exec('echo high | sudo tee -a /sys/class/gpio/gpio26/direction');
        exec('echo high | sudo tee -a /sys/class/gpio/gpio44/direction');
        exec('sleep 1');
        }

	private function openDoor() {
		exec('echo low | sudo tee -a /sys/class/gpio/gpio' . $this->gpioNumber . '/direction');
		exec('sleep 1');
		exec('echo high | sudo tee -a /sys/class/gpio/gpio' . $this->gpioNumber . '/direction');
	}

	private function prepareRelay() {
		exec('echo high | sudo tee -a /sys/class/gpio/gpio' . $this->gpioNumber . '/direction');
	}

	private function relayOn() {
		exec('echo low | sudo tee -a /sys/class/gpio/gpio' . $this->gpioNumber . '/direction');
	}

	private function relayOff() {
		exec('echo high | sudo tee -a /sys/class/gpio/gpio' . $this->gpioNumber . '/direction');
	}
}
