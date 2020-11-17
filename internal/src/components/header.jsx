import './header.scss'

import React from 'react'
import PropTypes from 'prop-types'

export default function Header({
	isShowingBanner,
	userName,
	onClickAboutOrBannerLink,
	onClickCloseBanner,
	onClickLogOut
}) {
	return (
		<div className="obo-header">
			<div className="wrapper">
				<div className="obo-header--left-side">
					<img className="obo-classic-logo" src={'./assets/images/viewer/obojobo-logo.svg'} />

					<button className="header-btn" onClick={e => onClickAboutOrBannerLink(e)}>
						About
					</button>
				</div>

				{isShowingBanner ? (
					<div className="banner-header">
						<span>What&apos;s different?</span>
						<button
							className="banner-header--modal-button"
							onClick={e => onClickAboutOrBannerLink(e)}
						>
							Click here to find out about the new look and our new version
						</button>
						<button className="banner-header--close-button" onClick={onClickCloseBanner}>
							&#10005;
						</button>
					</div>
				) : null}

				<div className="obo-header--right-side">
					<p>{userName}</p>
					<button className="header-btn" onClick={e => onClickLogOut(e)}>
						Logout
					</button>
				</div>
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
	onClickLogOut: PropTypes.func.isRequired,
	onClickCloseBanner: PropTypes.func.isRequired
}
