import React from 'react'
import Button from './button'
import './repository-page.scss'

const RepositoryPage = () => (
	<div className="repository--wrapper">
		<header>Header</header>
		<div className="content-wrapper">
			<div className="content-sidebar">Sidebar</div>
			<div className="content-main">Content<Button text="BUTTON!"/></div>
		</div>
		<footer>Footer</footer>
	</div>
)
export default RepositoryPage
