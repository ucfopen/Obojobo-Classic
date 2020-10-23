import React from 'react'
import PropTypes from 'prop-types'

export default function InstanceSection({ instance }) {
	return (
		<div>{instance ? 'Selected instance id: ' + instance.instID : 'Nothing selected (@TODO)'}</div>
	)
}

InstanceSection.propTypes = {
	instance: PropTypes.oneOfType([null, PropTypes.object])
}
