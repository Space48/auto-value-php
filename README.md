# AutoValue PHP

*Generated immutable value classes for PHP7.1+*

[![Build Status](https://travis-ci.org/Space48/auto-value-php.svg?branch=master)](https://travis-ci.org/Space48/auto-value-php)

*AutoValue PHP is a port of [Google AutoValue] (Kevin Bourrillion, Éamonn
McManus) from Java to PHP.*

**Value classes** are increasingly common in PHP projects. These are classes for
which you want to treat any two instances with suitably equal field values as
interchangeable.

Writing these classes by hand the first time is not too bad, with the aid of a
few helper methods and IDE templates. But once written they continue to burden
reviewers, editors and future readers. Their wide expanses of boilerplate
sharply decrease the signal-to-noise ratio of your code... and they love to
harbor hard-to-spot bugs.

AutoValue provides an easier way to create immutable value classes, with a lot
less code and less room for error, while **not restricting your freedom** to
code almost any aspect of your class exactly the way you want it.

For more information, consult the [detailed documentation](docs/index.md).

[Google AutoValue]: https://github.com/google/auto/blob/master/value
