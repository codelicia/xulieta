🌹 Xulieta — WIP
================

**Xulieta** is a light php binary that find code snippets thought out 
documentation files — as for example `*.md`, `*.markdown` and `*.rst` 
— and lint the pieces of code, so you can find basic documentation errors.


**NOTE**: For now we just lint PHP code. 

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

### Configuration

**Xulieta** tries to find a `xulieta.yaml` file in the root of your project
with the following configuration format:

```yaml
xulieta:
    plugins:
        - Codelicia\Xulieta\Format\MarkdownDocumentationFormat
        - Codelicia\Xulieta\Format\RstDocumentationFormat
    exclude_dirs:
        - vendor/
        - node_modules/
```

- `plugins`: listing of all formats handlers
- `exclude_dirs`: excluded directory

### Author

- [Jefersson Nathan](@malukenho) 
- [Alexandre Eher](@eher) 
