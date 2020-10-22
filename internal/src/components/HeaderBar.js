import React from 'react'
import PropTypes from 'prop-types'

export default function HeaderBar({ userName }) {
	return <div>@TODO</div>
}

HeaderBar.defaultProps = {
	isShowingBanner: true,
}

HeaderBar.propTypes = {
	isShowingBanner: PropTypes.bool,
	userName: PropTypes.string.isRequired,
	onClickAboutOrBannerLink: PropTypes.func.isRequired,
	onClickLogOut: PropTypes.func.isRequired,
}
