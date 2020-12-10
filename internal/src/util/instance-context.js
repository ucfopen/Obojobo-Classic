import React from 'react'

const state = {
	instance: null,
	setInstance: instance => {
		state.instance = instance
	}
}

export const InstanceContext = React.createContext(state)
