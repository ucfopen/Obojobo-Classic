import './media-view.scss'

import React from 'react'
import PropTypes from 'prop-types'

const DeprecationNotice = () => {
	return (
		<div className="deprecation-notice">
			This item can&apos;t be previewed as Flash is no longer supported.{' '}
			<a
				target="_blank"
				rel="noreferrer"
				href="https://www.adobe.com/products/flashplayer/end-of-life.html"
			>
				More info...
			</a>
		</div>
	)
}

const renderMediaItem = ({ mediaID, title, itemType, meta }) => {
	switch (itemType) {
		case 'pic':
			return <img className="pic" src={`https://obojobo.ucf.edu/media/${mediaID}`} />

		case 'kogneato':
			return (
				<div className="kogneato">
					<img src={meta.img} width="60" />
					<span>
						Materia Widget: <b>{title}</b>
					</span>
					<a target="_blank" rel="noreferrer" href={meta.preview_url}>
						Click to preview in a new window
					</a>
				</div>
			)

		case 'swf':
			return (
				<div className="swf">
					<div className="about">
						Flash SWF: <b>{title}</b>
					</div>
					<DeprecationNotice />
				</div>
			)

		case 'flv':
			return (
				<div className="flv">
					<div className="about">
						FLV Video: <b>{title}</b>
					</div>
					<DeprecationNotice />
				</div>
			)

		case 'mp3':
			return <audio className="mp3" src={`https://obojobo.ucf.edu/media/${mediaID}`} />
	}
}

export default function MediaView({ media }) {
	return <div className="repository--media-view">{renderMediaItem(media)}</div>
}

MediaView.propTypes = {
	media: PropTypes.shape({
		mediaID: PropTypes.number,
		title: PropTypes.string,
		itemType: PropTypes.oneOf(['pic', 'kogneato', 'swf', 'flv', 'mp3']),
		meta: PropTypes.oneOfType([
			PropTypes.number,
			PropTypes.shape({
				preview_url: PropTypes.string,
				img: PropTypes.string
			})
		])
	})
}
