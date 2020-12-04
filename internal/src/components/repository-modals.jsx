import './repository-modals.scss'

import React, { useEffect } from 'react'
import ReactModal from 'react-modal'
import Button from './button'

const RepositoryModals = ({ instanceName, modal, onCloseModal, className = '' }) => {
	useEffect(() => {
		ReactModal.setAppElement('#repository')
	}, [])

	return modal ? (
		<ReactModal
			isOpen={true}
			contentLabel={instanceName}
			className={`repository--modal ${className}`}
			overlayClassName="repository--modal-overlay"
			onRequestClose={onCloseModal}
		>
			<div>
				<div className="top-bar">
					<div className="module-title">{instanceName}</div>
					<Button type="text" ariaLabel="Close" onClick={onCloseModal} text="×" />
				</div>
				<div className="modal-content">
					<modal.component {...modal.props} onClose={onCloseModal} />
				</div>
			</div>
		</ReactModal>
	) : null
}

ReactModal.setAppElement('#react-app');

export default RepositoryModals
