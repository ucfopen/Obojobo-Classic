import './header.scss'

import React from 'react'
import PropTypes from 'prop-types'

import ObojoboLogo from '../../../assets/images/viewer/obojobo-logo.svg'

export default function Header(props) {
	return (
		<div className="obo-header">
			<div className="obo-header--left-side">
				<img className="obo-classic-logo" src={ObojoboLogo} />

				<button className="header-btn" onClick={e => props.onClickAboutOrBannerLink(e)}>
					About
				</button>
			</div>

			<div className="banner-header">
				<span>What&apos;s different?</span>
				<button
					className="banner-header--modal-button"
					onClick={e => props.onClickAboutOrBannerLink(e)}
				>
					Click here to find out about the new look and our new version
				</button>
				<button className="banner-header--close-button">&#10005;</button>
			</div>

			<div className="obo-header--right-side">
				<p>{props.userName}</p>
				<button className="header-btn" onClick={e => props.onClickLogOut(e)}>
					Logout
				</button>
			</div>
		</div>
	)
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
