🌹 Xulieta — WIP
================

**Xulieta** is a light php binary that find code snippets thought out documentation files — for now
it searches for `*.md`, `*.markdown` and `*.rst` file extensions — and lint the piece of code tagged
as `php`, so it can find basic documentation errors.

### Installation

```shell script
composer require codelicia/xulieta 
```

### Checking for errors

<table>
<tr>
<td><img src="./meme.jpg"  alt="Xulieta" width="300" height="214"/></td> 
<td>
In order to lint the basics of documentation structure, one just needs to provide a path for a 
directory or file to be linted.

```shell script
./vendor/bin/xulieta check:erromeu <directory>
```
</td>
</tr>
</table>

### Author

- [Jefersson Nathan](@malukenho) 
- [Alexandre Eher](@eher) 
