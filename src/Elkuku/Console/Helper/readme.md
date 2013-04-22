In general, to generate a progress bar you will call the constructor, then call
the update() method in a loop.

You can use the reset() method to reuse one object to display another bar. Its
parameters are the same as the constructor.

The Constructor expects 5 to 6 arguments:
- The first argument is the format string used to display the progress
  bar. It may (and should) contain placeholders that the class will
  replace with information like the progress bar itself, the progress in
  percent, and so on. Current placeholders are:
    %bar%         The progress bar
    %current%     The current value
    %max%         The maximum malue (the "target" value)
    %fraction%    The same as %current%/%max%
    %percent%     The status in percent
    %elapsed%     The elapsed time
    %estimate%    An estimate for the remaining time
  More placeholders will follow. A format string like:
  "* stuff.tar %fraction% KB [%bar%] %percent%"
  will lead to a bar looking like this:
  "* stuff.tar 391/900 KB [=====>---------]  43.44%"
- The second argument is the string that is going to fill the progress
  bar. In the above example, the string "=>" was used. If the string you
  pass is too short (like "=>" in this example), the leftmost character
  is used to pad it to the needed size. If the string you pass is too long,
  excessive characters are stripped from the left.
- The third argument is the string that fills the "empty" space in the
  progress bar. In the above example, that would be "-". If the string
  you pass is too short (like "-" in this example), the rightmost
  character is used to pad it to the needed size. If the string you pass
  is too short, excessive characters are stripped from the right.
- The fourth argument specifies the width of the display. If the options
  are left untouched, it will tell how many characters the display should
  use in total. If the "absolute_width" option is set to false, it tells
  how many characters the actual bar (that replaces the %bar%
  placeholder) should use.
- The fifth argument is the target number of the progress bar. For
  example, if you wanted to display a progress bar for a download of a
  file that is 115 KB big, you would pass 115 here.
- The sixth argument optional. If passed, it should contain an array of
  options. For example, passing array('absolute_width' => false) would
  set the absolute_width option to false. Current options are:

```
    option             | def.  |  meaning
    --------------------------------------------------------------------
    percent_precision  | 2     |  Number of decimal places to show when
                       |       |  displaying the percentage.
    fraction_precision | 0     |  Number of decimal places to show when
                       |       |  displaying the current or target
                       |       |  number.
    percent_pad        | ' '   |  Character to use when padding the
                       |       |  percentage to a fixed size. Senseful
                       |       |  values are ' ' and '0', but any are
                       |       |  possible.
    fraction_pad       | ' '   |  Character to use when padding max and
                       |       |  current number to a fixed size.
                       |       |  Senseful values are ' ' and '0', but
                       |       |  any are possible.
    width_absolute     | true  |  If the width passed as an argument
                       |       |  should mean the total size (true) or
                       |       |  the width of the bar alone.
    ansi_terminal      | false |  If this option is true, a better
                       |       |  (faster) method for erasing the bar is
                       |       |  used.
    ansi_clear         | false |  If the bar should be cleared everytime
    num_datapoints     | 5     |  How many datapoints to use to create
                       |       |  the estimated remaining time
```

The update() method expects just one parameter, the current status (somewhere
between 0 and the target number passed to the constructor) and refreshes the
display (or starts it, if it's the first call).
