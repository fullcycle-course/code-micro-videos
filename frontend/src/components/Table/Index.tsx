import React from 'react';
import MUIDataTable, {MUIDataTableColumn} from "mui-datatables";

interface ITable {
    title?: string;
    columns: MUIDataTableColumn[];
    data: Array<object | number[] | string[]>
}

const Table = ({title = '', columns, data }: ITable) => {
    // componentDidMount - será executado uma única vez.
    return (
        <div>
            <MUIDataTable
                title="Listagem de categorias"
                columns={columns}
                data={data}
            />
        </div>
    );
};


export default Table;