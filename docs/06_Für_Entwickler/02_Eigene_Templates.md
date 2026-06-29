Eigene Templates können wie gewohnt über TypoScript definiert werden.

```typo3_typoscript
# Plugin Konfiguration
plugin.tx_fpsocial {
	view {
		templateRootPaths {
			0 = EXT:fp_social/Resources/Private/Templates/
			1 = {$plugin.tx_fpsocial.view.templateRootPath}
		}
		partialRootPaths {
			0 = EXT:fp_social/Resources/Private/Partials/
			1 = {$plugin.tx_fpsocial.view.partialRootPath}
		}
		layoutRootPaths {
			0 = EXT:fp_social/Resources/Private/Layouts/
			1 = {$plugin.tx_fpsocial.view.layoutRootPath}
		}
	}
}

# Module Konfiguration
module.tx_fpsocial {
	view {
		templateRootPaths {
			0 = EXT:fp_social/Resources/Private/Backend/Templates/
			1 = {$module.tx_fpsocial.view.templateRootPath}
		}
		partialRootPaths {
			0 = EXT:fp_social/Resources/Private/Backend/Partials/
			1 = {$module.tx_fpsocial.view.partialRootPath}
		}
		layoutRootPaths {
			0 = EXT:fp_social/Resources/Private/Backend/Layouts/
			1 = {$module.tx_fpsocial.view.layoutRootPath}
		}
	}
}
```
# Partials für jedes Soziale Netzwerk
Bitte beachten Sie, dass jedes Soziale Netzwerk seine eigenen Partials verwendet welche sich im Frontend unter `EXT:fp_social/Resources/Private/Partials/Network/<network>` und im Backend unter `EXT:fp_social/Resources/Private/Backend/Partials/Network/<network>` befinden.
`<network>` entspricht hierbei dem Wert, den die Klasse des Sozialen Netzwerkes durch die Funktion `Class::getPartialFolder()`zurückgibt.