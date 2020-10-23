import React from 'react'
import PropTypes from 'prop-types'

export default function Header({
	isShowingBanner,
	userName,
	onClickAboutOrBannerLink,
	onClickLogOut
}) {
	return <div>Header @TODO</div>
}

Header.defaultProps = {
	isShowingBanner: true
}

Header.propTypes = {
	isShowingBanner: PropTypes.bool,
	userName: PropTypes.string.isRequired,
	onClickAboutOrBannerLink: PropTypes.func.isRequired,
	onClickLogOut: PropTypes.func.isRequired
}
