import React from 'react'
import ReactDOM from 'react-dom'
import RepositoryPage from './components/repository-page'
import { ReactQueryDevtools } from 'react-query-devtools'

const App = () => (
	<>
		<RepositoryPage />
		<ReactQueryDevtools initialIsOpen />
	</>
)

ReactDOM.render(<App />, document.getElementById('react-app'))
