<?php
/**
 * @copyright  (c) 2007 Stefan Walk
 * @license    MIT License
 * @author:    Stefan Walk <et@php.net>
 * @author     Nikolai Plath <https://github.com/elkuku>
 *
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
*/

namespace Elkuku\Console\Helper;

/**
 * Class to display a progressbar in the console
 *
 * @version   0.5.2
 * @category  Console
 * @package   Console_ProgressBar
 * @author    Stefan Walk <et@php.net>
 * @license   MIT License
 * @since     ¿
 */
class ConsoleProgressBar
{
	/**
	 * Skeleton for use with sprintf
	 */
	protected $skeleton;

	/**
	 * The bar gets filled with this
	 */
	protected $bar;

	/**
	 * The width of the bar
	 */
	protected $barLen;

	/**
	 * The total width of the display
	 */
	protected $totalLen;

	/**
	 * The position of the counter when the job is `done'
	 */
	protected $targetNum;

	/**
	 * Options, like the precision used to display the numbers
	 */
	protected $options = array();

	/**
	 * Length to erase
	 */
	protected $rLen = 0;

	/**
	 * When the progress started
	 */
	protected $startTime = null;

	protected $rateDataPoints = array();

	/**
	 * Time when the bar was last drawn
	 */
	protected $lastUpdateTime = 0.0;

	/**
	 * Indicates the first run status.
	 *
	 * @var boolean
	 */
	protected $firstRun;

	/**
	 * Constructor, sets format and size
	 *
	 * See the reset() method for documentation.
	 *
	 * @param   string   $formatString  The format string
	 * @param   string   $bar           The string filling the progress bar
	 * @param   string   $preFill       The string filling empty space in the bar
	 * @param   integer  $width         The width of the display
	 * @param   float    $targetNum     The target number for the bar
	 * @param   array    $options       Options for the progress bar
	 *
	 * @see reset
	 */
	public function __construct($formatString, $bar, $preFill, $width, $targetNum, $options = array())
	{
		$this->reset($formatString, $bar, $preFill, $width, $targetNum, $options);
	}

	/**
	 * Re-sets format and size.
	 *
	 * <pre>
	 * The reset method expects 5 to 6 arguments:
	 * - The first argument is the format string used to display the progress
	 *   bar. It may (and should) contain placeholders that the class will
	 *   replace with information like the progress bar itself, the progress in
	 *   percent, and so on. Current placeholders are:
	 *     %bar%         The progress bar
	 *     %current%     The current value
	 *     %max%         The maximum value (the "target" value)
	 *     %fraction%    The same as %current%/%max%
	 *     %percent%     The status in percent
	 *     %elapsed%     The elapsed time
	 *     %estimate%    An estimate of how long the progress will take
	 *   More placeholders will follow. A format string like:
	 *   "* stuff.tar %fraction% KB [%bar%] %percent%"
	 *   will lead to a bar looking like this:
	 *   "* stuff.tar 391/900 KB [=====>---------]  43.44%"
	 * - The second argument is the string that is going to fill the progress
	 *   bar. In the above example, the string "=>" was used. If the string you
	 *   pass is too short (like "=>" in this example), the leftmost character
	 *   is used to pad it to the needed size. If the string you pass is too long,
	 *   excessive characters are stripped from the left.
	 * - The third argument is the string that fills the "empty" space in the
	 *   progress bar. In the above example, that would be "-". If the string
	 *   you pass is too short (like "-" in this example), the rightmost
	 *   character is used to pad it to the needed size. If the string you pass
	 *   is too short, excessive characters are stripped from the right.
	 * - The fourth argument specifies the width of the display. If the options
	 *   are left untouched, it will tell how many characters the display should
	 *   use in total. If the "absolute_width" option is set to false, it tells
	 *   how many characters the actual bar (that replaces the %bar%
	 *   placeholder) should use.
	 * - The fifth argument is the target number of the progress bar. For
	 *   example, if you wanted to display a progress bar for a download of a
	 *   file that is 115 KB big, you would pass 115 here.
	 * - The sixth argument optional. If passed, it should contain an array of
	 *   options. For example, passing array('absolute_width' => false) would
	 *   set the absolute_width option to false. Current options are:
	 *
	 *     option             | def.  |  meaning
	 *     --------------------------------------------------------------------
	 *     percent_precision  | 2     |  Number of decimal places to show when
	 *                        |       |  displaying the percentage.
	 *     fraction_precision | 0     |  Number of decimal places to show when
	 *                        |       |  displaying the current or target
	 *                        |       |  number.
	 *     percent_pad        | ' '   |  Character to use when padding the
	 *                        |       |  percentage to a fixed size. Senseful
	 *                        |       |  values are ' ' and '0', but any are
	 *                        |       |  possible.
	 *     fraction_pad       | ' '   |  Character to use when padding max and
	 *                        |       |  current number to a fixed size.
	 *                        |       |  Senseful values are ' ' and '0', but
	 *                        |       |  any are possible.
	 *     width_absolute     | true  |  If the width passed as an argument
	 *                        |       |  should mean the total size (true) or
	 *                        |       |  the width of the bar alone.
	 *     ansi_terminal      | false |  If this option is true, a better
	 *                        |       |  (faster) method for erasing the bar is
	 *                        |       |  used. CAUTION - this is known to cause
	 *                        |       |  problems with some terminal emulators,
	 *                        |       |  for example Eterm.
	 *     ansi_clear         | false |  If the bar should be cleared every time
	 *     num_datapoints     | 5     |  How many datapoints to use to create
	 *                        |       |  the estimated remaining time
	 *     min_draw_interval  | 0.0   |  If the last call to update() was less
	 *                        |       |  than this amount of seconds ago,
	 *                        |       |  don't update.
	 * </pre>
	 *
	 * @param   string   $formatString  The format string
	 * @param   string   $bar           The string filling the progress bar
	 * @param   string   $preFill       The string filling empty space in the bar
	 * @param   integer  $width         The width of the display
	 * @param   float    $targetNum     The target number for the bar
	 * @param   array    $options       Options for the progress bar
	 *
	 * @return bool
	 */
	public function reset($formatString, $bar, $preFill, $width, $targetNum, $options = array())
	{
		if ($targetNum == 0)
		{
			trigger_error("PEAR::Console_ProgressBar: Using a target number equal to 0 is invalid, setting to 1 instead");
			$this->targetNum = 1;
		}
		else
		{
			$this->targetNum = $targetNum;
		}

		$default_options = array(
			'percent_precision'  => 2,
			'fraction_precision' => 0,
			'percent_pad'        => ' ',
			'fraction_pad'       => ' ',
			'width_absolute'     => true,
			'ansi_terminal'      => false,
			'ansi_clear'         => false,
			'num_datapoints'     => 5,
			'min_draw_interval'  => 0.0,
		);

		$intOpts = array();

		foreach ($default_options as $key => $value)
		{
			if (!isset($options[$key]))
			{
				$intOpts[$key] = $value;
			}
			else
			{
				settype($options[$key], gettype($value));
				$intOpts[$key] = $options[$key];
			}
		}

		$this->options = $options = $intOpts;

		// Placeholder
		$cur    = '%2$\'' . $options['fraction_pad']{0} . strlen((int) $targetNum) . '.'
			. $options['fraction_precision'] . 'f';
		$max    = $cur;
		$max{1} = 3;

		// Pre php-4.3.7 %3.2f meant 3 characters before . and two after
		// php-4.3.7 and later it means 3 characters for the whole number

		if (version_compare(PHP_VERSION, '4.3.7', 'ge'))
		{
			$padding = 4 + $options['percent_precision'];
		}
		else
		{
			$padding = 3;
		}

		$perc = '%4$\'' . $options['percent_pad']{0} . $padding . '.'
			. $options['percent_precision'] . 'f';

		$transitions = array(
			'%%'         => '%%',
			'%fraction%' => $cur . '/' . $max,
			'%current%'  => $cur,
			'%max%'      => $max,
			'%percent%'  => $perc . '%%',
			'%bar%'      => '%1$s',
			'%elapsed%'  => '%5$s',
			'%estimate%' => '%6$s',
		);

		$this->skeleton = strtr($formatString, $transitions);

		$sLen = strlen(sprintf($this->skeleton, '', 0, 0, 0, '00:00:00', '00:00:00'));

		if ($options['width_absolute'])
		{
			$bLen = $width - $sLen;
			$tLen = $width;
		}
		else
		{
			$tLen = $width + $sLen;
			$bLen = $width;
		}

		$lBar = str_pad($bar, $bLen, $bar{0}, STR_PAD_LEFT);
		$rBar = str_pad($preFill, $bLen, substr($preFill, -1, 1));

		$this->bar   = substr($lBar, -$bLen) . substr($rBar, 0, $bLen);
		$this->barLen  = $bLen;
		$this->totalLen  = $tLen;
		$this->firstRun = true;

		return true;
	}

	/**
	 * Updates the bar with new progress information
	 *
	 * @param   integer  $current  current position of the progress counter
	 *
	 * @return bool
	 */
	public function update($current)
	{
		$time = $this->fetchTime();
		$this->addDataPoint($current, $time);

		if ($this->firstRun)
		{
			if ($this->options['ansi_terminal'])
			{
				// Save cursor position
				echo "\x1b[s";
			}

			$this->firstRun      = false;
			$this->startTime = $this->fetchTime();
			$this->display($current);

			return;
		}

		if ($time - $this->lastUpdateTime < $this->options['min_draw_interval'] and $current != $this->targetNum)
		{
			return;
		}

		$this->erase();
		$this->display($current);
		$this->lastUpdateTime = $time;
	}

	/**
	 * Prints the bar. Usually, you don't need this method, just use update()
	 * which handles erasing the previously printed bar also. If you use a
	 * custom function (for whatever reason) to erase the bar, use this method.
	 *
	 * @param   integer  $current  current position of the progress counter
	 *
	 * @return boolean
	 */
	public function display($current)
	{
		$percent  = $current / $this->targetNum;
		$filled   = round($percent * $this->barLen);
		$visBar   = substr($this->bar, $this->barLen - $filled, $this->barLen);
		$elapsed  = $this->formatSeconds($this->fetchTime() - $this->startTime);
		$estimate = $this->formatSeconds($this->generateEstimate());

		$this->rLen = printf(
			$this->skeleton, $visBar, $current, $this->targetNum, $percent * 100, $elapsed, $estimate
		);

		// Fix for php-versions where printf doesn't return anything
		if (is_null($this->rLen))
		{
			// Fix for php versions between 4.3.7 and 5.x.y(?)
			$this->rLen = $this->totalLen;
		}
		elseif ($this->rLen < $this->totalLen)
		{
			echo str_repeat(' ', $this->totalLen - $this->rLen);
			$this->rLen = $this->totalLen;
		}

		return true;
	}

	/**
	 * Erases a previously printed bar.
	 *
	 * @param   boolean  $clear  if the bar should be cleared in addition to resetting the cursor position.
	 *
	 * @return boolean
	 */
	public function erase($clear = false)
	{
		if ($this->options['ansi_terminal'] and !$clear)
		{
			if ($this->options['ansi_clear'])
			{
				// Restore cursor position
				echo "\x1b[2K\x1b[u";
			}
			else
			{
				// Restore cursor position
				echo "\x1b[u";
			}
		}
		elseif (!$clear)
		{
			echo str_repeat(chr(0x08), $this->rLen);
		}
		else
		{
			echo str_repeat(chr(0x08), $this->rLen),
			str_repeat(chr(0x20), $this->rLen),
			str_repeat(chr(0x08), $this->rLen);
		}
	}

	/**
	 * Returns a string containing the formatted number of seconds.
	 *
	 * @param   float  $seconds  The number of seconds.
	 *
	 * @return string
	 */
	protected function formatSeconds($seconds)
	{
		$hou = floor($seconds / 3600);
		$min = floor(($seconds - $hou * 3600) / 60);
		$sec = $seconds - $hou * 3600 - $min * 60;

		if ($hou == 0)
		{
			if (version_compare(PHP_VERSION, '4.3.7', 'ge'))
			{
				$format = '%2$02d:%3$05.2f';
			}
			else
			{
				$format = '%2$02d:%3$02.2f';
			}
		}
		elseif ($hou < 100)
		{
			$format = '%02d:%02d:%02d';
		}
		else
		{
			$format = '%05d:%02d';
		}

		return sprintf($format, $hou, $min, $sec);
	}

	/**
	 * Fetch the time.
	 *
	 * @todo compat
	 *
	 * @return int|mixed|number
	 */
	protected function fetchTime()
	{
		if (!function_exists('microtime'))
		{
			return time();
		}

		if (version_compare(PHP_VERSION, '5.0.0', 'ge'))
		{
			return microtime(true);
		}

		return array_sum(explode(' ', microtime()));
	}

	/**
	 * Add a date point.
	 *
	 * @param   integer  $val   Pointer position.
	 * @param   string   $time  The time string.
	 *
	 * @return $this
	 */
	protected function addDataPoint($val, $time)
	{
		if (count($this->rateDataPoints) == $this->options['num_datapoints'])
		{
			array_shift($this->rateDataPoints);
		}

		$this->rateDataPoints[] = array(
			'time'  => $time,
			'value' => $val,
		);

		return $this;
	}

	/**
	 * Generate the estimated time.
	 *
	 * @return float
	 */
	protected function generateEstimate()
	{
		if (count($this->rateDataPoints) < 2)
		{
			return 0.0;
		}

		$first = $this->rateDataPoints[0];
		$last  = end($this->rateDataPoints);

		return ($this->targetNum - $last['value']) / ($last['value'] - $first['value']) * ($last['time'] - $first['time']);
	}
}
