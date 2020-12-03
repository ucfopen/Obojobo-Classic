import './media-view.scss'

import React from 'react'
import PropTypes from 'prop-types'

const DeprecationNotice = ({ mediaID }) => {
	return (
		<div className="deprecation-notice">
			This media can&apos;t be viewed as Flash has been retired.{' '}
			<a
				target="_blank"
				rel="noreferrer"
				href="https://www.adobe.com/products/flashplayer/end-of-life.html"
			>
				More info...
			</a>
			<a className="download-link" target="_blank" rel="noreferrer" href={`/media/${mediaID}`}>
				Download
			</a>
		</div>
	)
}

const renderMediaItem = ({ mediaID, title, itemType, meta, url }) => {
	switch (itemType) {
		case 'pic':
			return <img className={itemType} src={`/media/${mediaID}`} />

		case 'kogneato':
			return (
				<div className={itemType}>
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
				<div className={itemType}>
					<div className="about">
						Flash Media: <b>{title}</b>
					</div>
					<DeprecationNotice mediaID={mediaID} />
				</div>
			)

		case 'flv':
			return (
				<div className={itemType}>
					<div className="about">
						Flash Video: <b>{title}</b>
					</div>
					<DeprecationNotice mediaID={mediaID} />
				</div>
			)

		case 'youTube':
			return (
				<iframe
					className={itemType}
					width="300"
					height="169"
					src={`https://www.youtube.com/embed/${url}`}
					frameborder="0"
					allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
					allowfullscreen
				></iframe>
			)
	}
}

export default function MediaView({ media }) {
	return <div className="repository--media-view">{renderMediaItem(media)}</div>
}

MediaView.propTypes = {
	media: PropTypes.shape({
		mediaID: PropTypes.number,
		title: PropTypes.string,
		itemType: PropTypes.oneOf(['pic', 'kogneato', 'swf', 'flv', 'youTube']),
		meta: PropTypes.oneOfType([
			PropTypes.number,
			PropTypes.shape({
				preview_url: PropTypes.string,
				img: PropTypes.string
			})
		])
	})
}
