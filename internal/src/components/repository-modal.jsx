import './repository-modals.scss'

import React, { useEffect } from 'react'
import ReactModal from 'react-modal'
import Button from './button'

export default function RepositoryModal({ instanceName, children, onCloseModal, className = '' }){
	useEffect(() => {
		ReactModal.setAppElement('#repository')
	}, [])

	return children ? (
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
					{children}
				</div>
			</div>
		</ReactModal>
	) : null
}

ReactModal.setAppElement('#react-app');

