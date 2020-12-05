import React, { useCallback, useState } from 'react'

/* useToggleState

useToggleState is a convenience hook to consolidate togglable controls and state.
Example:

const myComponent = () => {
	const [visible, hide, show, text] = useToggleState()

	return (
		<div>
			<button onClick={() => {show('clicked!')}>
				Click to show
			</button>
			{visible
				? <div onClick={hide}>{text}</div>
				: null
			}
		</div>
	)
}

returns [isEnabled, disableFn, enableFn, enabledSettings]
calling enableFn(settings) will enable AND update the settings
calling disableFn() will disable AND clears the settings (there's no option to keep the settings)

If you don't need settings, just ignore the result: [isOn, disable, enable] = useToggleState()

You can set the  initial state and settings : [isOn, disable, enable, options] = useToggleState(true, {funky: true})

*/

export default function useToggleState(initialVisible = false, initialSettings = null){
	const [state, setState] = useState({enabled: initialVisible, settings: initialSettings})

	const [disableFn, enableFn] = React.useMemo(() => {
		return [
			() => {setState({enabled: false, settings: null})},
			(...settings) => {setState({enabled: true, settings})}
		]
	}, [])

	return [state.enabled, disableFn, enableFn, state.settings]
}
