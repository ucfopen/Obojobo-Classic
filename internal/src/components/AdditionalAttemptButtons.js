import React from 'react'
import PropTypes from 'prop-types'

export default function AdditionalAttemptButtons({ onClick }) {
	return <div>@TODO</div>
}

AdditionalAttemptButtons.propTypes = {
	isDecreaseEnabled: PropTypes.bool.isRequired,
	onClickDecrease: PropTypes.func.isRequired,
	onClickIncrease: PropTypes.func.isRequired,
}
