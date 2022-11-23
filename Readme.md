# TYPO3 Extension `t23_inline_container`

An extension to manage content elements inline in `b13/container` similar like possible in `gridelementsteam/gridelements` before.

|            | URL                                                                                                                        |
|------------|----------------------------------------------------------------------------------------------------------------------------|
| Repository | [https://github.com/team23/t23_inline_container/](https://github.com/team23/t23_inline_container/)                         |
| TER        | [https://extensions.typo3.org/extension/t23_inline_container](https://extensions.typo3.org/extension/t23_inline_container) |


## Background
The extension `gridelementsteam/gridelements` provided a field to add content elements inline.
Thereby it was possible to build more complex content pages using grids inside e.g. `georgringer/news`.
Since `b13/container` no longer uses a database field in the container itself, this is no longer possible.

This extension basically adds this field again to every container CType to provide the same behaviour as before with `gridelementsteam/gridelements`.

## Features
Adds a field "Content elements" to all container to make contained elements editable inline.

![](Resources/Public/Images/Screenshot.png)

## Installation & configuration
There is no configuration needed, just install with `composer req team23/t23-inline-container`.
The field will be added automatically to every registered container.

## Compatibility

| `t23_inline_container` | TYPO3 |
|------------------------|-------|
| 0.x                    | 11    |