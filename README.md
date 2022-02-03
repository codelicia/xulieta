<p align="center">
  <img src="./meme.jpg" alt="Xulieta" width="300" />
</p>

<h1 align="center"> 🌹 XULIETA </h1>
<h3 align="center"> Xulieta is a light php binary that lint documentation snippets. </h3>

**Xulieta** is a light php binary that find code snippets thought out
documentation files — as for example `*.md`, `*.markdown` and `*.rst`
— and lint the pieces of code, so you can find basic documentation errors.

**NOTE**: For now we just lint PHP code.

### Installation

```shell script
composer require codelicia/xulieta
```

### Checking for errors

In order to lint the basics of documentation structure, one just needs to provide a path for a
directory or file to be linted.

```shell script
./vendor/bin/xulieta check:erromeu <directory>
```

### Integration with GitHub Actions

We provide out  of the box an  `output` format that you can  use to have
automatic  feedback from  GitHub  CI.  That is  done  by specifying  the
`checkstyle` output and passing it to some external binary that does the
commenting.

We recommend the usage of [cs2pr](https://github.com/staabm/annotate-pull-request-from-checkstyle).

```
./vendor/bin/xulieta check:erromeu <directory> --output=checkstyle | cs2pr
```

#### Commenting example

<img src="./github-action.png"  alt="Codelicia\Xulieta" />

### Advanced Configuration

**Xulieta** tries to find a `.xulieta.xml` file in the root of your project
with the following configuration format:

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<xulieta xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="./vendor/codelicia/xulieta/xulieta.xsd">

    <parser>Codelicia\Xulieta\Parser\MarkdownParser</parser>
    <parser>Codelicia\Xulieta\Parser\RstParser</parser>

    <validator>Codelicia\Xulieta\Validator\PhpValidator</validator>

    <outputFormatters>Codelicia\Xulieta\Output\Checkstyle</outputFormatters>

    <exclude>vendor</exclude>
    <exclude>node_modules</exclude>
</xulieta>
```

- `parser`: listing of all parses to handle file formats based in the extention name
- `validator`: performs verification on a given code block
- `outputFormatters`: personalized output formatter
- `exclude`: excluded directory or files

## Plugins

`Xulieta` will automatically scan dependencies to see if there is 
any package that is providing default configurations.

If you want your plugin to take advantage of that functionality,
we expect you to provide some information on your `composer.json`
file, ie:

```json
{
  "extra": {
    "xulieta": {
      "parser": ["Malukenho\\QuoPrimumTempore\\JsonParser"],
      "validator": ["Malukenho\\QuoPrimumTempore\\JsonValidator"]
    }
  }
}
```

## Contributors ✨

Thanks goes to these wonderful people ([emoji key](https://allcontributors.org/docs/en/emoji-key)):

<!-- ALL-CONTRIBUTORS-LIST:START - Do not remove or modify this section -->
<!-- prettier-ignore-start -->
<!-- markdownlint-disable -->
<table>
  <tr>
    <td align="center"><a href="http://about:blank"><img src="https://avatars0.githubusercontent.com/u/398034?v=4" width="100px;" alt=""/><br /><sub><b>Alexandre Eher</b></sub></a><br /><a href="#infra-EHER" title="Infrastructure (Hosting, Build-Tools, etc)">🚇</a> <a href="#maintenance-EHER" title="Maintenance">🚧</a> <a href="https://github.com/codelicia/xulieta/commits?author=EHER" title="Code">💻</a></td>
    <td align="center"><a href="https://twitter.com/malukenho"><img src="https://avatars2.githubusercontent.com/u/3275172?v=4" width="100px;" alt=""/><br /><sub><b>Jefersson Nathan</b></sub></a><br /><a href="#infra-malukenho" title="Infrastructure (Hosting, Build-Tools, etc)">🚇</a> <a href="#maintenance-malukenho" title="Maintenance">🚧</a> <a href="https://github.com/codelicia/xulieta/commits?author=malukenho" title="Code">💻</a></td>
    <td align="center"><a href="https://airton.dev"><img src="https://avatars1.githubusercontent.com/u/6540546?v=4" width="100px;" alt=""/><br /><sub><b>Airton Zanon</b></sub></a><br /><a href="https://github.com/codelicia/xulieta/pulls?q=is%3Apr+reviewed-by%3Aairtonzanon" title="Reviewed Pull Requests">👀</a></td>
  </tr>
</table>

<!-- markdownlint-enable -->
<!-- prettier-ignore-end -->
<!-- ALL-CONTRIBUTORS-LIST:END -->

This project follows the [all-contributors](https://github.com/all-contributors/all-contributors) specification. Contributions of any kind welcome!
