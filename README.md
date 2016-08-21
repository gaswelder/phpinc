# phpinc

It's a command-line script that creates PHP source compilations
replacing `require` commands with text of the corresponding files. It
doesn't change the code in any way beyond that.

	$ phpinc [-l <search-path>]... <main-file> > result.php


## Why on Earth?..

The need in such a thing came about after one web script grew out to be a
standalone server program with numerous inclusions. I felt better
deploying just one compiled script instead of synchronizing hundreds of
files.

I used to concatenate source files, but the order of files then
mattered, and there were not supposed to be any `require` commands in
the source. So replacing `require`'s was more convenient than simple
concatenation: the order didn't matter and the original script could be
run as well as the compiled one.

Also, it allowed to optimize the size a little if libraries were
used because not all files from those were typically included. It also
made possible to stash libraries somewhere else and just provide the
path to them using `-l` flag just as with a C compiler:

	$ phpinc -l /libs/php foo.php > foo-c.php


## Quirks

There are limitations which shouldn't probably bother anyone since this
whole deed with PHP is not serious. If you happen to be in a situation
where a PHP script becomes so sprawled, it will most likely be better to
rewrite the whole thing in a real compiled language.

Only `require` commands at the top of a file are checked. `include`,
`include_once` and `require_once` are ignored.

SPL class loaders are not supported. That would require actually
parsing PHP code. Besides that, PHP 5 broke its file-indifference when
they added "namespaces": a "namespaced" file can't be pasted anywhere.
